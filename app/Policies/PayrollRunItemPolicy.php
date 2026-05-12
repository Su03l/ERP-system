<?php

namespace App\Policies;

use App\Models\PayrollRunItem;
use App\Models\User;

class PayrollRunItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'payslips.view');
    }

    public function view(User $user, PayrollRunItem $payrollRunItem): bool
    {
        return $this->sameCompany($user, $payrollRunItem->company_id)
            && $user->hasPermission('payslips.view', $payrollRunItem->company_id);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, PayrollRunItem $payrollRunItem): bool
    {
        return false;
    }

    public function delete(User $user, PayrollRunItem $payrollRunItem): bool
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
