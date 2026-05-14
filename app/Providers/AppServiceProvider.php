<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * SaaS platform abilities must stay outside tenant permission shortcuts.
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

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach (self::PLATFORM_ABILITIES as $ability) {
            Gate::define($ability, function (User $user): bool {
                return $user->company_id === null;
            });
        }

        Gate::define('financial_reports.view', fn (User $user): bool => $user->company_id !== null && $user->hasPermission('financial_reports.view', $user->company_id));
        Gate::define('financial_reports.export', fn (User $user): bool => $user->company_id !== null && $user->hasPermission('financial_reports.export', $user->company_id));
        Gate::define('asset_custody.view', fn (User $user): bool => $user->company_id !== null && $user->hasPermission('asset_custody.view', $user->company_id));
        Gate::define('asset_custody.create', fn (User $user): bool => $user->company_id !== null && $user->hasPermission('asset_custody.create', $user->company_id));
        Gate::define('asset_custody.approve', fn (User $user): bool => $user->company_id !== null && $user->hasPermission('asset_custody.approve', $user->company_id));
        Gate::define('asset_custody.return', fn (User $user): bool => $user->company_id !== null && $user->hasPermission('asset_custody.return', $user->company_id));

        Gate::before(function (User $user, string $ability): ?bool {
            if (in_array($ability, self::PLATFORM_ABILITIES, true)) {
                return null;
            }

            if ($user->hasPermission($ability)) {
                return true;
            }

            return null;
        });
    }
}
