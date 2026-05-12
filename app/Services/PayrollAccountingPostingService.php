<?php

namespace App\Services;

use App\Enums\PayrollRunStatus;
use App\Models\PayrollRun;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use LogicException;

class PayrollAccountingPostingService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function postApprovedPayrollRun(PayrollRun $payrollRun, ?User $actor = null): never
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User || $actor->company_id !== $payrollRun->company_id || ! $actor->hasPermission('payroll_runs.approve', $payrollRun->company_id)) {
            throw new AuthorizationException('You are not authorized to post payroll to accounting.');
        }

        if ($payrollRun->status !== PayrollRunStatus::Approved) {
            throw new LogicException('Only approved payroll runs can be posted to accounting.');
        }

        $this->auditLogger->log(
            action: 'payroll_accounting.posting_requested',
            auditable: $payrollRun,
            metadata: ['status' => 'accounting_module_not_ready'],
            user: $actor,
            company: $payrollRun->company_id,
        );

        throw new LogicException('Payroll accounting posting is not available until the accounting module is implemented.');
    }
}
