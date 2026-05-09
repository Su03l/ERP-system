<?php

namespace App\Policies;

use App\Models\EmployeeDocument;
use App\Models\User;

class EmployeeDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null
            && $user->hasPermission('employee_documents.view', $user->company_id);
    }

    public function view(User $user, EmployeeDocument $employeeDocument): bool
    {
        return $this->belongsToUsersCompany($user, $employeeDocument)
            && $user->hasPermission('employee_documents.view', $employeeDocument->company_id);
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null
            && $user->hasPermission('employee_documents.create', $user->company_id);
    }

    public function update(User $user, EmployeeDocument $employeeDocument): bool
    {
        return $this->belongsToUsersCompany($user, $employeeDocument)
            && $user->hasPermission('employee_documents.update', $employeeDocument->company_id);
    }

    public function delete(User $user, EmployeeDocument $employeeDocument): bool
    {
        return $this->belongsToUsersCompany($user, $employeeDocument)
            && $user->hasPermission('employee_documents.delete', $employeeDocument->company_id);
    }

    public function restore(User $user, EmployeeDocument $employeeDocument): bool
    {
        return false;
    }

    public function forceDelete(User $user, EmployeeDocument $employeeDocument): bool
    {
        return false;
    }

    private function belongsToUsersCompany(User $user, EmployeeDocument $employeeDocument): bool
    {
        return $user->company_id !== null && $user->company_id === $employeeDocument->company_id;
    }
}
