<?php

namespace App\Policies;

use App\Models\PayrollPeriod;
use App\Models\User;

class PayrollPeriodPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'payroll_periods.view');
    }

    public function view(User $user, PayrollPeriod $payrollPeriod): bool
    {
        return $this->sameCompany($user, $payrollPeriod->company_id)
            && $user->hasPermission('payroll_periods.view', $payrollPeriod->company_id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'payroll_periods.create');
    }

    public function update(User $user, PayrollPeriod $payrollPeriod): bool
    {
        return $this->sameCompany($user, $payrollPeriod->company_id)
            && $user->hasPermission('payroll_periods.update', $payrollPeriod->company_id);
    }

    public function delete(User $user, PayrollPeriod $payrollPeriod): bool
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
