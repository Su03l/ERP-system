<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'accounts.view');
    }

    public function view(User $user, Account $account): bool
    {
        return $this->sameCompany($user, $account->company_id)
            && $user->hasPermission('accounts.view', $account->company_id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'accounts.create');
    }

    public function update(User $user, Account $account): bool
    {
        return $this->sameCompany($user, $account->company_id)
            && $user->hasPermission('accounts.update', $account->company_id);
    }

    public function delete(User $user, Account $account): bool
    {
        return $this->sameCompany($user, $account->company_id)
            && $user->hasPermission('accounts.delete', $account->company_id);
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
