<?php

namespace App\Services;

use App\Models\User;
use App\Support\TenantContext;

class PermissionChecker
{
    public function __construct(private readonly TenantContext $tenantContext) {}

    public function userHasPermission(User $user, string $permissionKey, ?int $companyId = null): bool
    {
        $companyId ??= $this->tenantContext->companyId();

        if ($companyId === null || $user->company_id !== $companyId) {
            return false;
        }

        return $user->roles()
            ->wherePivot('company_id', $companyId)
            ->whereHas('permissions', fn ($query) => $query->where('key', $permissionKey))
            ->exists();
    }
}
