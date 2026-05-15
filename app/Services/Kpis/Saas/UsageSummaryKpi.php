<?php

namespace App\Services\Kpis\Saas;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Models\Company;
use App\Models\UsageSnapshot;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class UsageSummaryKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('saas.usage_summary', 'saas', 'ملخص الاستخدام', 'Usage summary', 'subscriptions.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $latest = UsageSnapshot::query()
            ->whereIn('id', UsageSnapshot::query()->selectRaw('MAX(id)')->groupBy('company_id'))
            ->get();

        return $this->result($dateRange, $latest->count(), unit: 'companies', metadata: [
            'platform_scope' => true,
            'users_count' => (int) $latest->sum('users_count'),
            'employees_count' => (int) $latest->sum('employees_count'),
            'storage_usage_mb' => (int) $latest->sum('storage_usage_mb'),
            'active_modules_count' => (int) $latest->sum('active_modules_count'),
            'api_requests_count' => (int) $latest->sum('api_requests_count'),
            'exports_count' => (int) $latest->sum('exports_count'),
        ]);
    }
}
