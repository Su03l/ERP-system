<?php

namespace App\Policies;

use App\Models\AssetCategory;
use App\Models\User;

class AssetCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'asset_categories.view');
    }

    public function view(User $user, AssetCategory $assetCategory): bool
    {
        return $this->sameCompany($user, $assetCategory->company_id)
            && $user->hasPermission('asset_categories.view', $assetCategory->company_id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'asset_categories.create');
    }

    public function update(User $user, AssetCategory $assetCategory): bool
    {
        return $this->sameCompany($user, $assetCategory->company_id)
            && $user->hasPermission('asset_categories.update', $assetCategory->company_id);
    }

    public function delete(User $user, AssetCategory $assetCategory): bool
    {
        return $this->sameCompany($user, $assetCategory->company_id)
            && $user->hasPermission('asset_categories.delete', $assetCategory->company_id);
    }

    public function restore(User $user, AssetCategory $assetCategory): bool
    {
        return $this->delete($user, $assetCategory);
    }

    public function forceDelete(User $user, AssetCategory $assetCategory): bool
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
