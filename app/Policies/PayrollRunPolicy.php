<?php

namespace App\Policies;

use App\Models\PayrollRun;
use App\Models\User;

class PayrollRunPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'payroll_runs.view');
    }

    public function view(User $user, PayrollRun $payrollRun): bool
    {
        return $this->sameCompany($user, $payrollRun->company_id)
            && $user->hasPermission('payroll_runs.view', $payrollRun->company_id);
    }

    public function generate(User $user): bool
    {
        return $this->can($user, 'payroll_runs.generate');
    }

    public function approve(User $user, PayrollRun $payrollRun): bool
    {
        return $this->sameCompany($user, $payrollRun->company_id)
            && $user->hasPermission('payroll_runs.approve', $payrollRun->company_id);
    }

    public function reject(User $user, PayrollRun $payrollRun): bool
    {
        return $this->sameCompany($user, $payrollRun->company_id)
            && $user->hasPermission('payroll_runs.reject', $payrollRun->company_id);
    }

    public function export(User $user): bool
    {
        return $this->can($user, 'payroll_runs.export');
    }

    public function create(User $user): bool
    {
        return $this->generate($user);
    }

    public function update(User $user, PayrollRun $payrollRun): bool
    {
        return false;
    }

    public function delete(User $user, PayrollRun $payrollRun): bool
    {
        return false;
    }

    private function can(User $user, string $permission): bool
    {
        return $user->company_id !== null && $user->hasPermission($permission, $user->company_id);
    }

    private function sameCompany(User $user, int $companyId): bool
    {
        return $user->company_id !== null && $user->company_id === $companyId;
    }
}
