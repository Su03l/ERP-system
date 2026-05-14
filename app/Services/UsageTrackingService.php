<?php

namespace App\Services;

use App\DTOs\PlanLimitResult;
use App\Enums\CompanyModule;
use App\Models\Company;
use App\Models\UsageSnapshot;

class UsageTrackingService
{
    public function __construct(private readonly PlanLimitsService $planLimitsService) {}

    public function capture(Company $company): UsageSnapshot
    {
        return UsageSnapshot::create([
            'company_id' => $company->id,
            ...$this->currentUsage($company),
            'captured_at' => now(),
            'metadata' => ['source' => 'usage_tracking_service'],
        ]);
    }

    /**
     * @return array{users_count: int, employees_count: int, storage_usage_mb: int, active_modules_count: int, api_requests_count: int, exports_count: int}
     */
    public function currentUsage(Company $company): array
    {
        $settings = $company->settings ?? [];
        $enabledModules = data_get($settings, 'enabled_modules', []);
        $enabledModules = is_array($enabledModules) ? $enabledModules : [];

        return [
            'users_count' => $company->users()->count(),
            'employees_count' => $company->employees()->count(),
            'storage_usage_mb' => (int) data_get($settings, 'usage.storage_mb', 0),
            'active_modules_count' => count(array_intersect($enabledModules, CompanyModule::keys())),
            'api_requests_count' => (int) data_get($settings, 'usage.api_requests_count', 0),
            'exports_count' => $company->exportJobs()->count(),
        ];
    }

    public function latestSnapshot(Company $company): ?UsageSnapshot
    {
        return $company->usageSnapshots()->latest('captured_at')->first();
    }

    public function checkUsersLimit(Company $company, int $additionalUsers = 1): PlanLimitResult
    {
        $snapshot = $this->latestSnapshot($company);

        if ($snapshot === null) {
            return $this->planLimitsService->checkUsersLimit($company, $additionalUsers);
        }

        return $this->planLimitsService->checkNumericUsage(
            company: $company,
            key: 'users',
            current: $snapshot->users_count,
            requested: $additionalUsers,
        );
    }

    public function checkEmployeesLimit(Company $company, int $additionalEmployees = 1): PlanLimitResult
    {
        $snapshot = $this->latestSnapshot($company);

        if ($snapshot === null) {
            return $this->planLimitsService->checkEmployeesLimit($company, $additionalEmployees);
        }

        return $this->planLimitsService->checkNumericUsage(
            company: $company,
            key: 'employees',
            current: $snapshot->employees_count,
            requested: $additionalEmployees,
        );
    }

    public function checkStorageLimit(Company $company, int $additionalStorageMb = 0): PlanLimitResult
    {
        $snapshot = $this->latestSnapshot($company);
        $currentStorageMb = $snapshot?->storage_usage_mb ?? $this->currentUsage($company)['storage_usage_mb'];

        return $this->planLimitsService->checkStorageLimit($company, $currentStorageMb, $additionalStorageMb);
    }
}
