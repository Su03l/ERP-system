<?php

namespace App\Services\Kpis\Saas;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Enums\SubscriptionStatus;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class TrialTenantsKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('saas.trial_tenants', 'saas', 'المستأجرون التجريبيون', 'Trial tenants', 'subscriptions.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $value = CompanySubscription::query()->where('status', SubscriptionStatus::Trialing->value)->distinct('company_id')->count('company_id');

        return $this->result($dateRange, $value, unit: 'tenants', metadata: ['platform_scope' => true]);
    }
}
