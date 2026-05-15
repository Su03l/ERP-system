<?php

namespace App\Services\Kpis\Saas;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Enums\CompanyAddOnStatus;
use App\Models\Company;
use App\Models\CompanyAddOn;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class AddOnRevenueKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('saas.add_on_revenue', 'saas', 'إيرادات الإضافات', 'Add-on revenue', 'add_ons.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $value = (float) CompanyAddOn::query()
            ->with('addOn')
            ->where('status', CompanyAddOnStatus::Active->value)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->get()
            ->sum(fn (CompanyAddOn $companyAddOn): float => (float) $companyAddOn->addOn?->price_monthly);

        return $this->result($dateRange, round($value, 2), unit: 'currency', metadata: ['platform_scope' => true]);
    }
}
