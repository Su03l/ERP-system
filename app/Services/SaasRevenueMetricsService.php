<?php

namespace App\Services;

use App\Enums\BillingCycle;
use App\Enums\CompanyAddOnStatus;
use App\Enums\SubscriptionInvoiceStatus;
use App\Enums\SubscriptionStatus;
use App\Models\CompanyAddOn;
use App\Models\CompanySubscription;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class SaasRevenueMetricsService
{
    /**
     * @param  array{from?: string, until?: string}  $filters
     * @return array<string, mixed>
     */
    public function summary(User $actor, array $filters = []): array
    {
        Gate::forUser($actor)->authorize('subscription_invoices.view');

        $from = isset($filters['from']) ? Carbon::parse($filters['from'])->startOfDay() : null;
        $until = isset($filters['until']) ? Carbon::parse($filters['until'])->endOfDay() : null;
        $mrr = $this->monthlyRecurringRevenue();

        return [
            'metrics' => [
                [
                    'key' => 'mrr',
                    'label' => __('saas.metrics.mrr'),
                    'value' => $mrr,
                ],
                [
                    'key' => 'arr',
                    'label' => __('saas.metrics.arr'),
                    'value' => round($mrr * 12, 2),
                    'metadata' => ['placeholder' => true],
                ],
                [
                    'key' => 'active_subscriptions',
                    'label' => __('saas.metrics.active_subscriptions'),
                    'value' => CompanySubscription::query()->where('status', SubscriptionStatus::Active->value)->count(),
                ],
                [
                    'key' => 'trial_companies',
                    'label' => __('saas.metrics.trial_companies'),
                    'value' => CompanySubscription::query()->where('status', SubscriptionStatus::Trialing->value)->distinct('company_id')->count('company_id'),
                ],
                [
                    'key' => 'cancelled_subscriptions',
                    'label' => __('saas.metrics.cancelled_subscriptions'),
                    'value' => CompanySubscription::query()->where('status', SubscriptionStatus::Cancelled->value)->count(),
                ],
                [
                    'key' => 'overdue_invoices',
                    'label' => __('saas.metrics.overdue_invoices'),
                    'value' => $this->overdueInvoicesCount(),
                ],
                [
                    'key' => 'add_on_revenue',
                    'label' => __('saas.metrics.add_on_revenue'),
                    'value' => $this->activeAddOnMonthlyRevenue(),
                ],
            ],
            'revenue_by_plan' => $this->revenueByPlan($from, $until)->values()->all(),
            'filters' => [
                'from' => $from?->toDateString(),
                'until' => $until?->toDateString(),
            ],
        ];
    }

    private function monthlyRecurringRevenue(): float
    {
        return (float) CompanySubscription::query()
            ->with('plan')
            ->where('status', SubscriptionStatus::Active->value)
            ->get()
            ->sum(function (CompanySubscription $subscription): float {
                $plan = $subscription->plan;

                if ($plan === null) {
                    return 0.0;
                }

                if ($subscription->billing_cycle === BillingCycle::Yearly) {
                    return round(((float) $plan->price_yearly) / 12, 2);
                }

                return (float) $plan->price_monthly;
            });
    }

    private function overdueInvoicesCount(): int
    {
        return SubscriptionInvoice::query()
            ->where(function ($query): void {
                $query->where('status', SubscriptionInvoiceStatus::Overdue->value)
                    ->orWhere(function ($query): void {
                        $query->whereIn('status', [
                            SubscriptionInvoiceStatus::Open->value,
                            SubscriptionInvoiceStatus::PartiallyPaid->value,
                        ])->whereNotNull('due_date')
                            ->where('due_date', '<', now()->toDateString());
                    });
            })
            ->count();
    }

    private function activeAddOnMonthlyRevenue(): float
    {
        return (float) CompanyAddOn::query()
            ->with('addOn')
            ->where('status', CompanyAddOnStatus::Active->value)
            ->where(function ($query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->get()
            ->sum(fn (CompanyAddOn $companyAddOn): float => (float) $companyAddOn->addOn?->price_monthly);
    }

    /**
     * @return Collection<int, array{plan_id: int|null, plan_code: string|null, plan_name: string|null, paid_amount: float}>
     */
    private function revenueByPlan(?Carbon $from, ?Carbon $until): Collection
    {
        return SubscriptionInvoice::query()
            ->with('subscription.plan')
            ->whereIn('status', [
                SubscriptionInvoiceStatus::Paid->value,
                SubscriptionInvoiceStatus::PartiallyPaid->value,
            ])
            ->when($from !== null, fn ($query) => $query->where('invoice_date', '>=', $from->toDateString()))
            ->when($until !== null, fn ($query) => $query->where('invoice_date', '<=', $until->toDateString()))
            ->get()
            ->groupBy(fn (SubscriptionInvoice $invoice): string => (string) ($invoice->subscription?->plan_id ?? 'none'))
            ->map(function (Collection $invoices): array {
                /** @var SubscriptionInvoice $first */
                $first = $invoices->first();
                $plan = $first->subscription?->plan;

                return [
                    'plan_id' => $plan?->id,
                    'plan_code' => $plan?->code,
                    'plan_name' => app()->getLocale() === 'en' ? $plan?->name_en ?? $plan?->name_ar : $plan?->name_ar,
                    'paid_amount' => round((float) $invoices->sum('paid_amount'), 2),
                ];
            });
    }
}
