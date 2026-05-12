<?php

namespace App\Actions;

use App\Enums\PayrollRunStatus;
use App\Models\PayrollRun;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\WorkflowExecutionService;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ApprovePayrollRun
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
        private readonly WorkflowExecutionService $workflowExecutionService,
    ) {}

    /**
     * @throws AuthorizationException
     */
    public function handle(PayrollRun $payrollRun, ?User $actor = null, ?string $comment = null): PayrollRun
    {
        $actor = $this->resolveActor($actor);
        $this->ensureTenant($payrollRun, $actor);

        return DB::transaction(function () use ($actor, $comment, $payrollRun): PayrollRun {
            $oldValues = $payrollRun->attributesToArray();
            $payrollRun->loadMissing('workflowInstance');

            if ($payrollRun->workflowInstance !== null && $payrollRun->workflowInstance->status === 'pending') {
                $this->workflowExecutionService->approve($payrollRun->workflowInstance, $actor, $comment);
                $payrollRun->workflowInstance->refresh();
            } else {
                Gate::forUser($actor)->authorize('approve', $payrollRun);
            }

            if ($payrollRun->workflowInstance === null || $payrollRun->workflowInstance->status === 'completed') {
                $payrollRun->forceFill([
                    'status' => PayrollRunStatus::Approved,
                    'approved_by' => $actor->id,
                    'approved_at' => now(),
                ])->save();
            } else {
                $payrollRun->forceFill([
                    'status' => PayrollRunStatus::PendingApproval,
                ])->save();
            }

            $this->auditLogger->log(
                action: 'payroll_run.approved',
                auditable: $payrollRun,
                oldValues: $oldValues,
                newValues: $payrollRun->refresh()->attributesToArray(),
                metadata: ['comment' => $comment],
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
            throw new AuthorizationException('An authenticated user is required to approve payroll runs.');
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
}
