<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'assets.view');
    }

    public function view(User $user, Asset $asset): bool
    {
        return $this->sameCompany($user, $asset->company_id)
            && $user->hasPermission('assets.view', $asset->company_id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'assets.create');
    }

    public function update(User $user, Asset $asset): bool
    {
        return $this->sameCompany($user, $asset->company_id)
            && $user->hasPermission('assets.update', $asset->company_id);
    }

    public function delete(User $user, Asset $asset): bool
    {
        return $this->sameCompany($user, $asset->company_id)
            && $user->hasPermission('assets.delete', $asset->company_id);
    }

    public function export(User $user): bool
    {
        return $this->can($user, 'assets.export');
    }

    public function restore(User $user, Asset $asset): bool
    {
        return $this->delete($user, $asset);
    }

    public function forceDelete(User $user, Asset $asset): bool
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
