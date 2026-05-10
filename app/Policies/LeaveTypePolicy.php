<?php

namespace App\Policies;

use App\Models\LeaveType;
use App\Models\User;

class LeaveTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'leave_types.view');
    }

    public function view(User $user, LeaveType $leaveType): bool
    {
        return $this->sameCompany($user, $leaveType->company_id) && $user->hasPermission('leave_types.view', $leaveType->company_id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'leave_types.create');
    }

    public function update(User $user, LeaveType $leaveType): bool
    {
        return $this->sameCompany($user, $leaveType->company_id) && $user->hasPermission('leave_types.update', $leaveType->company_id);
    }

    public function delete(User $user, LeaveType $leaveType): bool
    {
        return $this->sameCompany($user, $leaveType->company_id) && $user->hasPermission('leave_types.delete', $leaveType->company_id);
    }

    public function restore(User $user, LeaveType $leaveType): bool
    {
        return false;
    }

    public function forceDelete(User $user, LeaveType $leaveType): bool
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
