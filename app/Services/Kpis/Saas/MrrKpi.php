<?php

namespace App\Services\Kpis\Saas;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class MrrKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('saas.mrr', 'saas', 'الإيراد الشهري المتكرر', 'MRR', 'subscription_invoices.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $value = (float) CompanySubscription::query()
            ->with('plan')
            ->where('status', SubscriptionStatus::Active->value)
            ->get()
            ->sum(function (CompanySubscription $subscription): float {
                if ($subscription->plan === null) {
                    return 0.0;
                }

                return $subscription->billing_cycle === BillingCycle::Yearly
                    ? round((float) $subscription->plan->price_yearly / 12, 2)
                    : (float) $subscription->plan->price_monthly;
            });

        return $this->result($dateRange, round($value, 2), unit: 'currency', metadata: ['platform_scope' => true]);
    }
}
