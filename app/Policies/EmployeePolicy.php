<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null
            && $user->hasPermission('employees.view', $user->company_id);
    }

    public function view(User $user, Employee $employee): bool
    {
        return $this->belongsToUsersCompany($user, $employee)
            && $user->hasPermission('employees.view', $employee->company_id);
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null
            && $user->hasPermission('employees.create', $user->company_id);
    }

    public function update(User $user, Employee $employee): bool
    {
        return $this->belongsToUsersCompany($user, $employee)
            && $user->hasPermission('employees.update', $employee->company_id);
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $this->belongsToUsersCompany($user, $employee)
            && $user->hasPermission('employees.delete', $employee->company_id);
    }

    public function viewSalary(User $user, Employee $employee): bool
    {
        return $this->belongsToUsersCompany($user, $employee)
            && $user->hasPermission('employees.view_salary', $employee->company_id);
    }

    public function updateSalary(User $user, Employee $employee): bool
    {
        return $this->belongsToUsersCompany($user, $employee)
            && $user->hasPermission('employees.update_salary', $employee->company_id);
    }

    public function restore(User $user, Employee $employee): bool
    {
        return $this->delete($user, $employee);
    }

    public function forceDelete(User $user, Employee $employee): bool
    {
        return false;
    }

    private function belongsToUsersCompany(User $user, Employee $employee): bool
    {
        return $user->company_id !== null && $user->company_id === $employee->company_id;
    }
}
