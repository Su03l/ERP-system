<?php

use App\DTOs\KpiDateRange;
use App\Enums\BillingCycle;
use App\Enums\CompanyAddOnStatus;
use App\Enums\SubscriptionInvoiceStatus;
use App\Enums\SubscriptionStatus;
use App\Models\AddOn;
use App\Models\Company;
use App\Models\CompanyAddOn;
use App\Models\CompanySubscription;
use App\Models\Plan;
use App\Models\SubscriptionInvoice;
use App\Models\UsageSnapshot;
use App\Services\Kpis\Saas\ActiveTenantsKpi;
use App\Services\Kpis\Saas\AddOnRevenueKpi;
use App\Services\Kpis\Saas\ExpiredSubscriptionsKpi;
use App\Services\Kpis\Saas\MrrKpi;
use App\Services\Kpis\Saas\OverdueSubscriptionInvoicesKpi;
use App\Services\Kpis\Saas\RevenueByPlanKpi;
use App\Services\Kpis\Saas\TrialTenantsKpi;
use App\Services\Kpis\Saas\UsageSummaryKpi;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('resolves platform SaaS KPI values without tenant scoping leakage', function () {
    $company = Company::factory()->create();
    $plan = Plan::factory()->create(['price_monthly' => 100, 'price_yearly' => 1200]);
    $subscription = CompanySubscription::factory()->for($company)->for($plan)->create([
        'status' => SubscriptionStatus::Active,
        'billing_cycle' => BillingCycle::Yearly,
    ]);
    CompanySubscription::factory()->create(['status' => SubscriptionStatus::Trialing]);
    CompanySubscription::factory()->create(['status' => SubscriptionStatus::Expired]);
    SubscriptionInvoice::query()->create([
        'company_id' => $company->id,
        'subscription_id' => $subscription->id,
        'invoice_number' => 'SUB-001',
        'invoice_date' => '2026-01-15',
        'due_date' => now()->subDay(),
        'status' => SubscriptionInvoiceStatus::Paid,
        'subtotal' => 100,
        'tax_amount' => 0,
        'discount_amount' => 0,
        'total_amount' => 100,
        'paid_amount' => 100,
        'balance_due' => 0,
        'currency' => 'SAR',
    ]);
    SubscriptionInvoice::query()->create([
        'company_id' => $company->id,
        'subscription_id' => $subscription->id,
        'invoice_number' => 'SUB-002',
        'invoice_date' => '2026-01-16',
        'status' => SubscriptionInvoiceStatus::Open,
        'due_date' => now()->subDay(),
        'subtotal' => 100,
        'tax_amount' => 0,
        'discount_amount' => 0,
        'total_amount' => 100,
        'paid_amount' => 0,
        'balance_due' => 100,
        'currency' => 'SAR',
    ]);
    $addOn = AddOn::factory()->create(['price_monthly' => 25]);
    CompanyAddOn::factory()->for($company)->for($addOn)->create(['status' => CompanyAddOnStatus::Active]);
    UsageSnapshot::factory()->for($company)->create(['users_count' => 2, 'employees_count' => 3]);

    $range = KpiDateRange::fromDates('2026-01-01', '2026-01-31');

    expect(app(MrrKpi::class)->resolve($company, $range)->value)->toBe(100.0)
        ->and(app(ActiveTenantsKpi::class)->resolve($company, $range)->value)->toBe(1)
        ->and(app(TrialTenantsKpi::class)->resolve($company, $range)->value)->toBe(1)
        ->and(app(ExpiredSubscriptionsKpi::class)->resolve($company, $range)->value)->toBe(1)
        ->and(app(OverdueSubscriptionInvoicesKpi::class)->resolve($company, $range)->value)->toBe(1)
        ->and(app(RevenueByPlanKpi::class)->resolve($company, $range)->value)->toBe(100.0)
        ->and(app(AddOnRevenueKpi::class)->resolve($company, $range)->value)->toBe(25.0)
        ->and(app(UsageSummaryKpi::class)->resolve($company, $range)->metadata['users_count'])->toBe(2);
});
