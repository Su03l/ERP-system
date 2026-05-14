<?php

namespace App\Services;

use App\Models\AddOn;
use App\Models\CompanyAddOn;
use App\Models\CompanySubscription;
use App\Models\Plan;
use App\Models\SubscriptionInvoice;
use App\Models\UsageSnapshot;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class SaasExportService
{
    public function __construct(private readonly SaasRevenueMetricsService $metricsService) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function plans(User $actor, array $filters = []): array
    {
        Gate::forUser($actor)->authorize('plans.view');

        return Plan::query()
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->orderBy('code')
            ->get()
            ->map(fn (Plan $plan): array => [
                'code' => $plan->code,
                'name' => $this->localizedName($plan->name_ar, $plan->name_en),
                'status' => $plan->status->label(),
                'price_monthly' => (float) $plan->price_monthly,
                'price_yearly' => $plan->price_yearly !== null ? (float) $plan->price_yearly : null,
                'currency' => $plan->currency,
            ])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function subscriptions(User $actor, array $filters = []): array
    {
        Gate::forUser($actor)->authorize('subscriptions.view');

        return CompanySubscription::query()
            ->with('company', 'plan')
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['plan_id']), fn ($query) => $query->where('plan_id', $filters['plan_id']))
            ->when(isset($filters['company_id']), fn ($query) => $query->where('company_id', $filters['company_id']))
            ->latest('id')
            ->get()
            ->map(fn (CompanySubscription $subscription): array => [
                'company' => $subscription->company?->name,
                'plan' => $subscription->plan?->code,
                'status' => $subscription->status->label(),
                'billing_cycle' => $subscription->billing_cycle->label(),
                'starts_at' => $subscription->starts_at?->toDateString(),
                'ends_at' => $subscription->ends_at?->toDateString(),
                'trial_ends_at' => $subscription->trial_ends_at?->toDateString(),
                'grace_ends_at' => $subscription->grace_ends_at?->toDateString(),
            ])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function subscriptionInvoices(User $actor, array $filters = []): array
    {
        Gate::forUser($actor)->authorize('subscription_invoices.view');

        return SubscriptionInvoice::query()
            ->with('company', 'subscription.plan')
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['company_id']), fn ($query) => $query->where('company_id', $filters['company_id']))
            ->when(isset($filters['from']), fn ($query) => $query->where('invoice_date', '>=', $filters['from']))
            ->when(isset($filters['until']), fn ($query) => $query->where('invoice_date', '<=', $filters['until']))
            ->latest('invoice_date')
            ->get()
            ->map(fn (SubscriptionInvoice $invoice): array => [
                'invoice_number' => $invoice->invoice_number,
                'company' => $invoice->company?->name,
                'plan' => $invoice->subscription?->plan?->code,
                'status' => $invoice->status->label(),
                'invoice_date' => $invoice->invoice_date?->toDateString(),
                'due_date' => $invoice->due_date?->toDateString(),
                'total_amount' => (float) $invoice->total_amount,
                'paid_amount' => (float) $invoice->paid_amount,
                'balance_due' => (float) $invoice->balance_due,
                'currency' => $invoice->currency,
            ])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function addOns(User $actor, array $filters = []): array
    {
        Gate::forUser($actor)->authorize('add_ons.view');

        return AddOn::query()
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->orderBy('code')
            ->get()
            ->map(fn (AddOn $addOn): array => [
                'code' => $addOn->code,
                'name' => $this->localizedName($addOn->name_ar, $addOn->name_en),
                'category' => $addOn->category,
                'feature_key' => $addOn->feature_key,
                'status' => $addOn->status->label(),
                'price_monthly' => (float) $addOn->price_monthly,
                'price_yearly' => $addOn->price_yearly !== null ? (float) $addOn->price_yearly : null,
            ])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function companyAddOns(User $actor, array $filters = []): array
    {
        Gate::forUser($actor)->authorize('company_add_ons.manage');

        return CompanyAddOn::query()
            ->with('company', 'addOn')
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['company_id']), fn ($query) => $query->where('company_id', $filters['company_id']))
            ->when(isset($filters['add_on_id']), fn ($query) => $query->where('add_on_id', $filters['add_on_id']))
            ->latest('id')
            ->get()
            ->map(fn (CompanyAddOn $companyAddOn): array => [
                'company' => $companyAddOn->company?->name,
                'add_on' => $companyAddOn->addOn?->code,
                'status' => $companyAddOn->status->label(),
                'starts_at' => $companyAddOn->starts_at?->toDateString(),
                'ends_at' => $companyAddOn->ends_at?->toDateString(),
            ])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function usageReports(User $actor, array $filters = []): array
    {
        Gate::forUser($actor)->authorize('subscriptions.view');

        return UsageSnapshot::query()
            ->with('company')
            ->when(isset($filters['company_id']), fn ($query) => $query->where('company_id', $filters['company_id']))
            ->when(isset($filters['from']), fn ($query) => $query->whereDate('captured_at', '>=', $filters['from']))
            ->when(isset($filters['until']), fn ($query) => $query->whereDate('captured_at', '<=', $filters['until']))
            ->latest('captured_at')
            ->get()
            ->map(fn (UsageSnapshot $snapshot): array => [
                'company' => $snapshot->company?->name,
                'users_count' => $snapshot->users_count,
                'employees_count' => $snapshot->employees_count,
                'storage_usage_mb' => $snapshot->storage_usage_mb,
                'active_modules_count' => $snapshot->active_modules_count,
                'api_requests_count' => $snapshot->api_requests_count,
                'exports_count' => $snapshot->exports_count,
                'captured_at' => $snapshot->captured_at?->toDateTimeString(),
            ])
            ->all();
    }

    /**
     * @param  array{from?: string, until?: string}  $filters
     * @return array<string, mixed>
     */
    public function revenueMetrics(User $actor, array $filters = []): array
    {
        return $this->metricsService->summary($actor, $filters);
    }

    private function localizedName(string $nameAr, ?string $nameEn): string
    {
        return app()->getLocale() === 'en' ? $nameEn ?? $nameAr : $nameAr;
    }
}
