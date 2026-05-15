<?php

namespace App\Support;

final class PlatformAbilities
{
    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return [
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
            'kpi.saas.view',
        ];
    }

    public static function contains(string $ability): bool
    {
        return in_array($ability, self::all(), true);
    }
}
