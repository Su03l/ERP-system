<?php

namespace App\Policies;

use App\Models\SalaryComponent;
use App\Models\User;

class SalaryComponentPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'salary_components.view');
    }

    public function view(User $user, SalaryComponent $salaryComponent): bool
    {
        return $this->sameCompany($user, $salaryComponent->company_id)
            && $user->hasPermission('salary_components.view', $salaryComponent->company_id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'salary_components.create');
    }

    public function update(User $user, SalaryComponent $salaryComponent): bool
    {
        return $this->sameCompany($user, $salaryComponent->company_id)
            && $user->hasPermission('salary_components.update', $salaryComponent->company_id);
    }

    public function delete(User $user, SalaryComponent $salaryComponent): bool
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
