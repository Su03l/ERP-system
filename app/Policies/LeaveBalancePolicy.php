<?php

namespace App\Policies;

use App\Models\LeaveBalance;
use App\Models\User;

class LeaveBalancePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'leave_balances.view');
    }

    public function view(User $user, LeaveBalance $leaveBalance): bool
    {
        return $this->sameCompany($user, $leaveBalance->company_id) && $user->hasPermission('leave_balances.view', $leaveBalance->company_id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'leave_balances.update');
    }

    public function update(User $user, LeaveBalance $leaveBalance): bool
    {
        return $this->sameCompany($user, $leaveBalance->company_id) && $user->hasPermission('leave_balances.update', $leaveBalance->company_id);
    }

    public function delete(User $user, LeaveBalance $leaveBalance): bool
    {
        return false;
    }

    public function restore(User $user, LeaveBalance $leaveBalance): bool
    {
        return false;
    }

    public function forceDelete(User $user, LeaveBalance $leaveBalance): bool
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
