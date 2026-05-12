<?php

namespace App\Providers;

use App\Models\User;
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
        Gate::define('financial_reports.view', fn (User $user): bool => $user->company_id !== null && $user->hasPermission('financial_reports.view', $user->company_id));
        Gate::define('financial_reports.export', fn (User $user): bool => $user->company_id !== null && $user->hasPermission('financial_reports.export', $user->company_id));

        Gate::before(function (User $user, string $ability): ?bool {
            if ($user->hasPermission($ability)) {
                return true;
            }

            return null;
        });
    }
}
