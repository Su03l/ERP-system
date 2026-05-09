<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null
            && $user->hasPermission('departments.view', $user->company_id);
    }

    public function view(User $user, Department $department): bool
    {
        return $this->belongsToUsersCompany($user, $department)
            && $user->hasPermission('departments.view', $department->company_id);
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null
            && $user->hasPermission('departments.create', $user->company_id);
    }

    public function update(User $user, Department $department): bool
    {
        return $this->belongsToUsersCompany($user, $department)
            && $user->hasPermission('departments.update', $department->company_id);
    }

    public function delete(User $user, Department $department): bool
    {
        return $this->belongsToUsersCompany($user, $department)
            && $user->hasPermission('departments.delete', $department->company_id);
    }

    public function restore(User $user, Department $department): bool
    {
        return $this->delete($user, $department);
    }

    public function forceDelete(User $user, Department $department): bool
    {
        return false;
    }

    private function belongsToUsersCompany(User $user, Department $department): bool
    {
        return $user->company_id !== null && $user->company_id === $department->company_id;
    }
}
