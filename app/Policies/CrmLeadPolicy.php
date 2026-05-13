<?php

namespace App\Policies;

use App\Models\CrmLead;
use App\Models\User;

class CrmLeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'crm_leads.view');
    }

    public function view(User $user, CrmLead $crmLead): bool
    {
        return $this->sameCompany($user, $crmLead->company_id)
            && $user->hasPermission('crm_leads.view', $crmLead->company_id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'crm_leads.create');
    }

    public function update(User $user, CrmLead $crmLead): bool
    {
        return $this->sameCompany($user, $crmLead->company_id)
            && $user->hasPermission('crm_leads.update', $crmLead->company_id);
    }

    public function delete(User $user, CrmLead $crmLead): bool
    {
        return $this->sameCompany($user, $crmLead->company_id)
            && $user->hasPermission('crm_leads.delete', $crmLead->company_id);
    }

    public function convert(User $user, CrmLead $crmLead): bool
    {
        return $this->sameCompany($user, $crmLead->company_id)
            && $user->hasPermission('crm_leads.convert', $crmLead->company_id);
    }

    public function restore(User $user, CrmLead $crmLead): bool
    {
        return $this->delete($user, $crmLead);
    }

    public function forceDelete(User $user, CrmLead $crmLead): bool
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
