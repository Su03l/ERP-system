<?php

namespace App\Policies;

use App\Models\CompanyDocument;
use App\Models\User;

class CompanyDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'company_documents.view');
    }

    public function view(User $user, CompanyDocument $companyDocument): bool
    {
        return $this->sameCompany($user, $companyDocument->company_id)
            && $user->hasPermission('company_documents.view', $companyDocument->company_id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'company_documents.create');
    }

    public function update(User $user, CompanyDocument $companyDocument): bool
    {
        return $this->sameCompany($user, $companyDocument->company_id)
            && $user->hasPermission('company_documents.update', $companyDocument->company_id);
    }

    public function delete(User $user, CompanyDocument $companyDocument): bool
    {
        return $this->sameCompany($user, $companyDocument->company_id)
            && $user->hasPermission('company_documents.delete', $companyDocument->company_id);
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
