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

class RejectPayrollRun
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
        private readonly WorkflowExecutionService $workflowExecutionService,
    ) {}

    /**
     * @throws AuthorizationException
     */
    public function handle(PayrollRun $payrollRun, ?User $actor = null, ?string $reason = null): PayrollRun
    {
        $actor = $this->resolveActor($actor);
        $this->ensureTenant($payrollRun, $actor);

        return DB::transaction(function () use ($actor, $payrollRun, $reason): PayrollRun {
            $oldValues = $payrollRun->attributesToArray();
            $payrollRun->loadMissing('workflowInstance');

            if ($payrollRun->workflowInstance !== null && $payrollRun->workflowInstance->status === 'pending') {
                $this->workflowExecutionService->reject($payrollRun->workflowInstance, $actor, $reason);
            } else {
                Gate::forUser($actor)->authorize('reject', $payrollRun);
            }

            $payrollRun->forceFill([
                'status' => PayrollRunStatus::Rejected,
            ])->save();

            $this->auditLogger->log(
                action: 'payroll_run.rejected',
                auditable: $payrollRun,
                oldValues: $oldValues,
                newValues: $payrollRun->refresh()->attributesToArray(),
                metadata: ['reason' => $reason],
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
            throw new AuthorizationException('An authenticated user is required to reject payroll runs.');
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
