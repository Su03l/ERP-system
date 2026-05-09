<?php

namespace App\Policies;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\User;

class AttendanceRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null
            && $user->hasPermission('attendance.view', $user->company_id);
    }

    public function view(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $this->belongsToUsersCompany($user, $attendanceRecord)
            && $user->hasPermission('attendance.view', $attendanceRecord->company_id);
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null
            && $user->hasPermission('attendance.create', $user->company_id);
    }

    public function update(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $this->belongsToUsersCompany($user, $attendanceRecord)
            && $user->hasPermission('attendance.update', $attendanceRecord->company_id);
    }

    public function delete(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $this->belongsToUsersCompany($user, $attendanceRecord)
            && $user->hasPermission('attendance.delete', $attendanceRecord->company_id);
    }

    public function clock(User $user, Employee $employee): bool
    {
        if ($user->company_id === null || $user->company_id !== $employee->company_id) {
            return false;
        }

        if ($user->employeeProfile?->id === $employee->id) {
            return true;
        }

        return $user->hasPermission('attendance.clock', $employee->company_id);
    }

    public function recalculate(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $this->belongsToUsersCompany($user, $attendanceRecord)
            && $user->hasPermission('attendance.recalculate', $attendanceRecord->company_id);
    }

    public function export(User $user): bool
    {
        return $user->company_id !== null
            && $user->hasPermission('attendance.export', $user->company_id);
    }

    public function restore(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return false;
    }

    public function forceDelete(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return false;
    }

    private function belongsToUsersCompany(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $user->company_id !== null && $user->company_id === $attendanceRecord->company_id;
    }
}
