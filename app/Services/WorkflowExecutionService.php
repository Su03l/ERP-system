<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowAction;
use App\Models\WorkflowInstance;
use App\Models\WorkflowStep;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WorkflowExecutionService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>|null  $payload
     *
     * @throws AuthorizationException
     */
    public function start(Workflow $workflow, User $requestedBy, ?Model $subject = null, ?array $payload = null): WorkflowInstance
    {
        $this->ensureTenant($workflow->company_id);

        return DB::transaction(function () use ($workflow, $requestedBy, $subject, $payload): WorkflowInstance {
            $firstStep = $workflow->steps()->first();

            $instance = WorkflowInstance::create([
                'company_id' => $workflow->company_id,
                'workflow_id' => $workflow->id,
                'current_step_id' => $firstStep?->id,
                'requested_by_id' => $requestedBy->id,
                'subject_type' => $subject?->getMorphClass(),
                'subject_id' => $subject?->getKey(),
                'status' => $firstStep ? 'pending' : 'completed',
                'payload' => $payload,
                'completed_at' => $firstStep ? null : now(),
            ]);

            $this->auditLogger->log(
                action: 'workflow.instance.started',
                auditable: $instance,
                newValues: $instance->only(['workflow_id', 'current_step_id', 'status', 'payload']),
                user: $requestedBy,
                company: $workflow->company_id,
            );

            return $instance->refresh();
        });
    }

    public function currentStep(WorkflowInstance $instance): ?WorkflowStep
    {
        return $instance->currentStep;
    }

    /**
     * @throws AuthorizationException
     */
    public function approve(WorkflowInstance $instance, User $actor, ?string $comment = null): WorkflowInstance
    {
        return $this->recordDecision($instance, $actor, 'approved', $comment);
    }

    /**
     * @throws AuthorizationException
     */
    public function reject(WorkflowInstance $instance, User $actor, ?string $comment = null): WorkflowInstance
    {
        return $this->recordDecision($instance, $actor, 'rejected', $comment);
    }

    /**
     * @throws AuthorizationException
     */
    public function returnBack(WorkflowInstance $instance, User $actor, ?string $comment = null): WorkflowInstance
    {
        return $this->recordDecision($instance, $actor, 'returned', $comment);
    }

    /**
     * @throws AuthorizationException
     */
    private function recordDecision(WorkflowInstance $instance, User $actor, string $decision, ?string $comment): WorkflowInstance
    {
        $instance->loadMissing('currentStep', 'workflow.steps');
        $this->ensureTenant($instance->company_id);
        $this->ensureActorCanAct($instance, $actor);

        return DB::transaction(function () use ($instance, $actor, $decision, $comment): WorkflowInstance {
            $oldValues = $instance->only(['current_step_id', 'status', 'completed_at']);
            $currentStep = $instance->currentStep;

            WorkflowAction::create([
                'company_id' => $instance->company_id,
                'workflow_instance_id' => $instance->id,
                'workflow_step_id' => $currentStep?->id,
                'acted_by_id' => $actor->id,
                'action' => $decision,
                'comment' => $comment,
                'metadata' => [],
                'acted_at' => now(),
            ]);

            match ($decision) {
                'approved' => $this->advanceAfterApproval($instance),
                'rejected' => $this->markRejected($instance),
                'returned' => $this->moveToPreviousStep($instance),
                default => null,
            };

            $this->auditLogger->log(
                action: "workflow.instance.{$decision}",
                auditable: $instance,
                oldValues: $oldValues,
                newValues: $instance->only(['current_step_id', 'status', 'completed_at']),
                metadata: ['comment' => $comment],
                user: $actor,
                company: $instance->company_id,
            );

            return $instance->refresh();
        });
    }

    private function advanceAfterApproval(WorkflowInstance $instance): void
    {
        $nextStep = $this->nextStep($instance);

        if ($nextStep === null) {
            $this->markCompleted($instance);

            return;
        }

        $instance->forceFill([
            'current_step_id' => $nextStep->id,
            'status' => 'pending',
        ])->save();
    }

    private function markCompleted(WorkflowInstance $instance): void
    {
        $instance->forceFill([
            'current_step_id' => null,
            'status' => 'completed',
            'completed_at' => now(),
        ])->save();
    }

    private function markRejected(WorkflowInstance $instance): void
    {
        $instance->forceFill([
            'status' => 'rejected',
            'completed_at' => now(),
        ])->save();
    }

    private function moveToPreviousStep(WorkflowInstance $instance): void
    {
        $previousStep = $this->previousStep($instance);

        if ($previousStep === null) {
            return;
        }

        $instance->forceFill([
            'current_step_id' => $previousStep->id,
            'status' => 'pending',
        ])->save();
    }

    private function nextStep(WorkflowInstance $instance): ?WorkflowStep
    {
        $currentOrder = $instance->currentStep?->order;

        if ($currentOrder === null) {
            return null;
        }

        return $instance->workflow->steps
            ->first(fn (WorkflowStep $step): bool => $step->order > $currentOrder);
    }

    private function previousStep(WorkflowInstance $instance): ?WorkflowStep
    {
        $currentOrder = $instance->currentStep?->order;

        if ($currentOrder === null) {
            return null;
        }

        return $instance->workflow->steps
            ->reverse()
            ->first(fn (WorkflowStep $step): bool => $step->order < $currentOrder);
    }

    /**
     * @throws AuthorizationException
     */
    public function ensureActorCanAct(WorkflowInstance $instance, User $actor): void
    {
        if ($actor->company_id !== $instance->company_id) {
            throw new AuthorizationException('The actor does not belong to this workflow company.');
        }

        $step = $instance->currentStep;

        if (! $step instanceof WorkflowStep) {
            throw new AuthorizationException('This workflow instance has no pending step.');
        }

        $canAct = match ($step->approver_type) {
            'user' => (string) $actor->id === $step->approver_value,
            'role' => $actor->roles()
                ->wherePivot('company_id', $instance->company_id)
                ->where('roles.id', (int) $step->approver_value)
                ->exists(),
            'permission' => $actor->hasPermission($step->approver_value, $instance->company_id),
            default => false,
        };

        if (! $canAct) {
            throw new AuthorizationException('The actor is not assigned to the current workflow step.');
        }
    }

    /**
     * @throws AuthorizationException
     */
    private function ensureTenant(int $companyId): void
    {
        if ($this->tenantContext->companyId() !== $companyId) {
            throw new AuthorizationException('The workflow does not belong to the current tenant.');
        }
    }
}
