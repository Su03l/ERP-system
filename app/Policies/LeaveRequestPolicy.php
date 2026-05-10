<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'leave_requests.view');
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        return $this->sameCompany($user, $leaveRequest->company_id)
            && ($user->hasPermission('leave_requests.view', $leaveRequest->company_id) || $this->ownsEmployeeProfile($user, $leaveRequest));
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        return $this->sameCompany($user, $leaveRequest->company_id)
            && ($user->hasPermission('leave_requests.create', $leaveRequest->company_id) || $this->ownsEmployeeProfile($user, $leaveRequest));
    }

    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        return false;
    }

    public function submit(User $user, LeaveRequest $leaveRequest): bool
    {
        return $this->update($user, $leaveRequest);
    }

    public function approve(User $user, LeaveRequest $leaveRequest): bool
    {
        return $this->sameCompany($user, $leaveRequest->company_id) && $user->hasPermission('leave_requests.approve', $leaveRequest->company_id);
    }

    public function reject(User $user, LeaveRequest $leaveRequest): bool
    {
        return $this->sameCompany($user, $leaveRequest->company_id) && $user->hasPermission('leave_requests.reject', $leaveRequest->company_id);
    }

    public function cancel(User $user, LeaveRequest $leaveRequest): bool
    {
        return $this->sameCompany($user, $leaveRequest->company_id) && ($user->hasPermission('leave_requests.cancel', $leaveRequest->company_id) || $this->ownsEmployeeProfile($user, $leaveRequest));
    }

    public function return(User $user, LeaveRequest $leaveRequest): bool
    {
        return $this->sameCompany($user, $leaveRequest->company_id) && $user->hasPermission('leave_requests.reject', $leaveRequest->company_id);
    }

    public function restore(User $user, LeaveRequest $leaveRequest): bool
    {
        return false;
    }

    public function forceDelete(User $user, LeaveRequest $leaveRequest): bool
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

    private function ownsEmployeeProfile(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->employeeProfile?->id === $leaveRequest->employee_id;
    }
}
