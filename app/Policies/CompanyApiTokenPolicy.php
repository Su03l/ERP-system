<?php

namespace App\Policies;

use App\Models\CompanyApiToken;
use App\Models\User;

class CompanyApiTokenPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'api_tokens.view');
    }

    public function view(User $user, CompanyApiToken $companyApiToken): bool
    {
        return $this->sameCompany($user, $companyApiToken)
            && $user->hasPermission('api_tokens.view', $companyApiToken->company_id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'api_tokens.create');
    }

    public function update(User $user, CompanyApiToken $companyApiToken): bool
    {
        return $this->sameCompany($user, $companyApiToken)
            && $user->hasPermission('api_tokens.revoke', $companyApiToken->company_id);
    }

    public function delete(User $user, CompanyApiToken $companyApiToken): bool
    {
        return $this->sameCompany($user, $companyApiToken)
            && $user->hasPermission('api_tokens.revoke', $companyApiToken->company_id);
    }

    public function revoke(User $user, CompanyApiToken $companyApiToken): bool
    {
        return $this->delete($user, $companyApiToken);
    }

    private function can(User $user, string $permission): bool
    {
        return $user->company_id !== null && $user->hasPermission($permission, $user->company_id);
    }

    private function sameCompany(User $user, CompanyApiToken $companyApiToken): bool
    {
        return $user->company_id !== null && $user->company_id === $companyApiToken->company_id;
    }
}
