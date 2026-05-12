<?php

namespace App\Actions;

use App\Enums\PayrollRunStatus;
use App\Models\PayrollRun;
use App\Models\User;
use App\Models\Workflow;
use App\Services\AuditLogger;
use App\Services\WorkflowExecutionService;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SubmitPayrollRunForApproval
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
        private readonly WorkflowExecutionService $workflowExecutionService,
    ) {}

    /**
     * @throws AuthorizationException
     */
    public function handle(PayrollRun $payrollRun, ?User $actor = null): PayrollRun
    {
        $actor = $this->resolveActor($actor);
        $this->ensureTenant($payrollRun, $actor);

        return DB::transaction(function () use ($actor, $payrollRun): PayrollRun {
            $payrollRun->loadMissing('company.payrollSetting');
            $oldValues = $payrollRun->attributesToArray();
            $approvalRequired = $payrollRun->company->payrollSetting?->payroll_approval_required ?? true;

            if (! $approvalRequired) {
                $this->markApproved($payrollRun, $actor, 'payroll_run.approval_not_required', $oldValues);

                return $payrollRun->refresh();
            }

            $workflow = $this->approvalWorkflow($payrollRun);

            if ($workflow instanceof Workflow) {
                $instance = $this->workflowExecutionService->start($workflow, $actor, $payrollRun, [
                    'payroll_run_id' => $payrollRun->id,
                    'payroll_period_id' => $payrollRun->payroll_period_id,
                    'run_number' => $payrollRun->run_number,
                    'net_amount' => $payrollRun->net_amount,
                ]);

                $payrollRun->forceFill([
                    'workflow_instance_id' => $instance->id,
                    'status' => $instance->status === 'completed'
                        ? PayrollRunStatus::Approved
                        : PayrollRunStatus::PendingApproval,
                    'approved_by' => $instance->status === 'completed' ? $actor->id : null,
                    'approved_at' => $instance->status === 'completed' ? now() : null,
                ])->save();

                $this->auditLogger->log(
                    action: 'payroll_run.submitted_for_approval',
                    auditable: $payrollRun,
                    oldValues: $oldValues,
                    newValues: $payrollRun->refresh()->attributesToArray(),
                    metadata: ['workflow_instance_id' => $instance->id],
                    user: $actor,
                    company: $payrollRun->company_id,
                );

                return $payrollRun;
            }

            if (Gate::forUser($actor)->allows('approve', $payrollRun)) {
                $this->markApproved($payrollRun, $actor, 'payroll_run.approved_without_workflow', $oldValues);

                return $payrollRun->refresh();
            }

            $payrollRun->forceFill([
                'status' => PayrollRunStatus::PendingApproval,
            ])->save();

            $this->auditLogger->log(
                action: 'payroll_run.pending_approval_without_workflow',
                auditable: $payrollRun,
                oldValues: $oldValues,
                newValues: $payrollRun->refresh()->attributesToArray(),
                metadata: [],
                user: $actor,
                company: $payrollRun->company_id,
            );

            return $payrollRun;
        });
    }

    /**
     * @throws AuthorizationException
     */
    private function resolveActor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to submit payroll runs for approval.');
        }

        return $actor;
    }

    /**
     * @throws AuthorizationException
     */
    private function ensureTenant(PayrollRun $payrollRun, User $actor): void
    {
        if ($this->tenantContext->companyId() !== $payrollRun->company_id || $actor->company_id !== $payrollRun->company_id) {
            throw new AuthorizationException('Payroll run does not belong to the current company.');
        }
    }

    private function approvalWorkflow(PayrollRun $payrollRun): ?Workflow
    {
        return Workflow::query()
            ->where('company_id', $payrollRun->company_id)
            ->where('module_key', 'payroll')
            ->where('trigger_type', 'payroll_run_approval')
            ->where('status', 'active')
            ->with('steps')
            ->latest('id')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $oldValues
     */
    private function markApproved(PayrollRun $payrollRun, User $actor, string $auditAction, array $oldValues): void
    {
        $payrollRun->forceFill([
            'status' => PayrollRunStatus::Approved,
            'approved_by' => $actor->id,
            'approved_at' => now(),
        ])->save();

        $this->auditLogger->log(
            action: $auditAction,
            auditable: $payrollRun,
            oldValues: $oldValues,
            newValues: $payrollRun->refresh()->attributesToArray(),
            metadata: [],
            user: $actor,
            company: $payrollRun->company_id,
        );
    }
}
