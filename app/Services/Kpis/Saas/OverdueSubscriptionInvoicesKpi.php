<?php

namespace App\Services\Kpis\Saas;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Enums\SubscriptionInvoiceStatus;
use App\Models\Company;
use App\Models\SubscriptionInvoice;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class OverdueSubscriptionInvoicesKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('saas.overdue_subscription_invoices', 'saas', 'فواتير الاشتراك المتأخرة', 'Overdue subscription invoices', 'subscription_invoices.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $value = SubscriptionInvoice::query()
            ->where(fn ($query) => $query->where('status', SubscriptionInvoiceStatus::Overdue->value)
                ->orWhere(fn ($query) => $query->whereIn('status', [SubscriptionInvoiceStatus::Open->value, SubscriptionInvoiceStatus::PartiallyPaid->value])
                    ->whereNotNull('due_date')
                    ->whereDate('due_date', '<', now()->toDateString())))
            ->count();

        return $this->result($dateRange, $value, unit: 'invoices', metadata: ['platform_scope' => true]);
    }
}
