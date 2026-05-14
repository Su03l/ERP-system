<?php

namespace App\Services;

use App\DTOs\PlanLimitResult;
use App\Enums\CompanyModule;
use App\Enums\SubscriptionStatus;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Plan;

class PlanLimitsService
{
    public function checkUsersLimit(Company $company, int $additionalUsers = 1): PlanLimitResult
    {
        return $this->checkNumericLimit(
            company: $company,
            key: 'users',
            current: $company->users()->count(),
            requested: $additionalUsers,
        );
    }

    public function checkEmployeesLimit(Company $company, int $additionalEmployees = 1): PlanLimitResult
    {
        return $this->checkNumericLimit(
            company: $company,
            key: 'employees',
            current: $company->employees()->count(),
            requested: $additionalEmployees,
        );
    }

    public function checkStorageLimit(Company $company, int $currentStorageMb, int $additionalStorageMb = 0): PlanLimitResult
    {
        return $this->checkNumericLimit(
            company: $company,
            key: 'storage_mb',
            current: $currentStorageMb,
            requested: $additionalStorageMb,
        );
    }

    public function moduleEnabled(Company $company, CompanyModule|string $module): PlanLimitResult
    {
        $moduleKey = $module instanceof CompanyModule ? $module->value : $module;
        $plan = $this->planFor($company);
        $enabledModules = $this->enabledModules($plan);
        $allowed = in_array($moduleKey, $enabledModules, true);

        return new PlanLimitResult(
            allowed: $allowed,
            key: 'modules',
            message: $this->message($allowed, 'modules'),
            metadata: [
                'module' => $moduleKey,
                'enabled_modules' => $enabledModules,
            ],
        );
    }

    public function apiAccess(Company $company): PlanLimitResult
    {
        return $this->checkFeature($company, 'api_access');
    }

    public function advancedReportsAccess(Company $company): PlanLimitResult
    {
        return $this->checkFeature($company, 'advanced_reports');
    }

    public function marketplaceAccess(Company $company): PlanLimitResult
    {
        return $this->checkFeature($company, 'marketplace');
    }

    public function activeSubscription(Company $company): ?CompanySubscription
    {
        return $company->subscriptions()
            ->with('plan')
            ->whereIn('status', [
                SubscriptionStatus::Trialing->value,
                SubscriptionStatus::Active->value,
                SubscriptionStatus::Grace->value,
            ])
            ->where('starts_at', '<=', now())
            ->where(function ($query): void {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->latest('id')
            ->first();
    }

    public function planFor(Company $company): ?Plan
    {
        return $this->activeSubscription($company)?->plan;
    }

    private function checkFeature(Company $company, string $key): PlanLimitResult
    {
        $plan = $this->planFor($company);
        $allowed = (bool) data_get($plan?->features ?? [], $key, false);

        return new PlanLimitResult(
            allowed: $allowed,
            key: $key,
            message: $this->message($allowed, $key),
        );
    }

    private function checkNumericLimit(Company $company, string $key, int|float $current, int|float $requested): PlanLimitResult
    {
        $plan = $this->planFor($company);
        $limit = data_get($plan?->limits ?? [], $key);

        if ($limit === null) {
            return new PlanLimitResult(
                allowed: true,
                key: $key,
                message: __('saas.limits.allowed_unlimited', ['limit' => __("saas.limit_keys.{$key}")]),
                limit: null,
                current: $current,
            );
        }

        $limit = (int) $limit;
        $projected = $current + $requested;
        $allowed = $projected <= $limit;

        return new PlanLimitResult(
            allowed: $allowed,
            key: $key,
            message: $this->message($allowed, $key),
            limit: $limit,
            current: $current,
            metadata: ['requested' => $requested, 'projected' => $projected],
        );
    }

    /**
     * @return array<int, string>
     */
    private function enabledModules(?Plan $plan): array
    {
        $features = $plan?->features ?? [];
        $modules = data_get($features, 'enabled_modules', data_get($features, 'modules', []));

        if (! is_array($modules)) {
            return [];
        }

        return array_values(array_intersect(array_unique($modules), CompanyModule::keys()));
    }

    private function message(bool $allowed, string $key): string
    {
        $messageKey = $allowed ? 'saas.limits.allowed' : 'saas.limits.denied';

        return __($messageKey, ['limit' => __("saas.limit_keys.{$key}")]);
    }
}
