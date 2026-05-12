<?php

namespace App\Policies;

use App\Models\EmployeeSalaryPackage;
use App\Models\User;

class EmployeeSalaryPackagePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'salary_packages.view');
    }

    public function view(User $user, EmployeeSalaryPackage $employeeSalaryPackage): bool
    {
        return $this->sameCompany($user, $employeeSalaryPackage->company_id)
            && $user->hasPermission('salary_packages.view', $employeeSalaryPackage->company_id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'salary_packages.create');
    }

    public function update(User $user, EmployeeSalaryPackage $employeeSalaryPackage): bool
    {
        return $this->sameCompany($user, $employeeSalaryPackage->company_id)
            && $user->hasPermission('salary_packages.update', $employeeSalaryPackage->company_id);
    }

    public function delete(User $user, EmployeeSalaryPackage $employeeSalaryPackage): bool
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
