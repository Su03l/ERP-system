<?php

namespace App\Policies;

use App\Models\PayrollSetting;
use App\Models\User;

class PayrollSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'payroll_settings.view');
    }

    public function view(User $user, PayrollSetting $payrollSetting): bool
    {
        return $this->sameCompany($user, $payrollSetting->company_id)
            && $user->hasPermission('payroll_settings.view', $payrollSetting->company_id);
    }

    public function update(User $user, PayrollSetting $payrollSetting): bool
    {
        return $this->sameCompany($user, $payrollSetting->company_id)
            && $user->hasPermission('payroll_settings.update', $payrollSetting->company_id);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function delete(User $user, PayrollSetting $payrollSetting): bool
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
