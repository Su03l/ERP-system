<?php

namespace App\Services;

use App\Models\User;
use App\Support\TenantContext;

class PermissionChecker
{
    /**
     * SaaS platform abilities must not be granted through tenant roles.
     *
     * @var array<int, string>
     */
    private const PLATFORM_ABILITIES = [
        'saas_settings.view',
        'saas_settings.update',
        'plans.view',
        'plans.create',
        'plans.update',
        'plans.delete',
        'subscriptions.view',
        'subscriptions.create',
        'subscriptions.update',
        'subscriptions.cancel',
        'subscription_invoices.view',
        'subscription_invoices.generate',
        'subscription_invoices.mark_paid',
        'add_ons.view',
        'add_ons.create',
        'add_ons.update',
        'add_ons.delete',
        'company_add_ons.manage',
    ];

    public function __construct(private readonly TenantContext $tenantContext) {}

    public function userHasPermission(User $user, string $permissionKey, ?int $companyId = null): bool
    {
        if (in_array($permissionKey, self::PLATFORM_ABILITIES, true)) {
            return false;
        }

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
