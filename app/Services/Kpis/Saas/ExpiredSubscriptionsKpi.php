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

class ExpiredSubscriptionsKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('saas.expired_subscriptions', 'saas', 'الاشتراكات المنتهية', 'Expired subscriptions', 'subscriptions.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $value = CompanySubscription::query()->where('status', SubscriptionStatus::Expired->value)->count();

        return $this->result($dateRange, $value, unit: 'subscriptions', metadata: ['platform_scope' => true]);
    }
}
