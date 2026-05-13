<?php

namespace App\Policies;

use App\Models\CrmContact;
use App\Models\User;

class CrmContactPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'crm_contacts.view');
    }

    public function view(User $user, CrmContact $crmContact): bool
    {
        return $this->sameCompany($user, $crmContact->company_id)
            && $user->hasPermission('crm_contacts.view', $crmContact->company_id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'crm_contacts.create');
    }

    public function update(User $user, CrmContact $crmContact): bool
    {
        return $this->sameCompany($user, $crmContact->company_id)
            && $user->hasPermission('crm_contacts.update', $crmContact->company_id);
    }

    public function delete(User $user, CrmContact $crmContact): bool
    {
        return $this->sameCompany($user, $crmContact->company_id)
            && $user->hasPermission('crm_contacts.delete', $crmContact->company_id);
    }

    public function restore(User $user, CrmContact $crmContact): bool
    {
        return $this->delete($user, $crmContact);
    }

    public function forceDelete(User $user, CrmContact $crmContact): bool
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
