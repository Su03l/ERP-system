<?php

namespace App\Policies;

use App\Models\AccountingSetting;
use App\Models\User;

class AccountingSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'accounting_settings.view');
    }

    public function view(User $user, AccountingSetting $accountingSetting): bool
    {
        return $this->sameCompany($user, $accountingSetting->company_id)
            && $user->hasPermission('accounting_settings.view', $accountingSetting->company_id);
    }

    public function update(User $user, AccountingSetting $accountingSetting): bool
    {
        return $this->sameCompany($user, $accountingSetting->company_id)
            && $user->hasPermission('accounting_settings.update', $accountingSetting->company_id);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function delete(User $user, AccountingSetting $accountingSetting): bool
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
