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
use Illuminate\Support\Collection;

class RevenueByPlanKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('saas.revenue_by_plan', 'saas', 'الإيراد حسب الخطة', 'Revenue by plan', 'subscription_invoices.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $values = SubscriptionInvoice::query()
            ->with('subscription.plan')
            ->whereIn('status', [SubscriptionInvoiceStatus::Paid->value, SubscriptionInvoiceStatus::PartiallyPaid->value])
            ->whereDate('invoice_date', '>=', $dateRange->start->toDateString())
            ->whereDate('invoice_date', '<=', $dateRange->end->toDateString())
            ->get()
            ->groupBy(fn (SubscriptionInvoice $invoice): string => (string) ($invoice->subscription?->plan_id ?? 'none'))
            ->map(function (Collection $invoices): array {
                $plan = $invoices->first()?->subscription?->plan;

                return [
                    'plan_id' => $plan?->id,
                    'plan_code' => $plan?->code,
                    'plan_name' => app()->getLocale() === 'en' ? $plan?->name_en ?? $plan?->name_ar : $plan?->name_ar,
                    'paid_amount' => round((float) $invoices->sum('paid_amount'), 2),
                ];
            })
            ->values()
            ->all();

        return $this->result($dateRange, round((float) array_sum(array_column($values, 'paid_amount')), 2), unit: 'currency', metadata: ['platform_scope' => true, 'values' => $values]);
    }
}
