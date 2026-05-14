<?php

namespace App\Providers;

use App\Models\User;
use App\Support\PlatformAbilities;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
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

        foreach (PlatformAbilities::all() as $ability) {
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
            if (PlatformAbilities::contains($ability)) {
                return null;
            }

            if ($user->hasPermission($ability)) {
                return true;
            }

            return null;
        });
    }
}
