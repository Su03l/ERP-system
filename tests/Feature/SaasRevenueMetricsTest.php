<?php

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
use App\Models\User;
use App\Services\SaasRevenueMetricsService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns platform revenue metrics from subscriptions and invoices', function () {
    $actor = User::factory()->create(['company_id' => null]);
    $monthlyPlan = Plan::factory()->create(['price_monthly' => 100, 'price_yearly' => 1000]);
    $yearlyPlan = Plan::factory()->create(['price_monthly' => 200, 'price_yearly' => 1200]);
    $monthlySubscription = CompanySubscription::factory()->for($monthlyPlan, 'plan')->create([
        'status' => SubscriptionStatus::Active,
        'billing_cycle' => BillingCycle::Monthly,
    ]);
    $yearlySubscription = CompanySubscription::factory()->for($yearlyPlan, 'plan')->create([
        'status' => SubscriptionStatus::Active,
        'billing_cycle' => BillingCycle::Yearly,
    ]);
    CompanySubscription::factory()->create(['status' => SubscriptionStatus::Trialing]);
    CompanySubscription::factory()->create(['status' => SubscriptionStatus::Cancelled]);
    SubscriptionInvoice::query()->create([
        'company_id' => $monthlySubscription->company_id,
        'subscription_id' => $monthlySubscription->id,
        'invoice_number' => 'SUB-MONTHLY',
        'status' => SubscriptionInvoiceStatus::Paid,
        'invoice_date' => now(),
        'subtotal' => 80,
        'tax_amount' => 0,
        'discount_amount' => 0,
        'total_amount' => 80,
        'paid_amount' => 80,
        'balance_due' => 0,
        'currency' => 'SAR',
    ]);
    SubscriptionInvoice::query()->create([
        'company_id' => $yearlySubscription->company_id,
        'subscription_id' => $yearlySubscription->id,
        'invoice_number' => 'SUB-YEARLY',
        'status' => SubscriptionInvoiceStatus::Open,
        'invoice_date' => now(),
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
    CompanyAddOn::factory()->for($addOn)->create(['status' => CompanyAddOnStatus::Active]);

    $summary = app(SaasRevenueMetricsService::class)->summary($actor);

    expect(collect($summary['metrics'])->firstWhere('key', 'mrr')['value'])->toBe(200.0)
        ->and(collect($summary['metrics'])->firstWhere('key', 'arr')['value'])->toBe(2400.0)
        ->and(collect($summary['metrics'])->firstWhere('key', 'trial_companies')['value'])->toBe(1)
        ->and(collect($summary['metrics'])->firstWhere('key', 'cancelled_subscriptions')['value'])->toBe(1)
        ->and(collect($summary['metrics'])->firstWhere('key', 'overdue_invoices')['value'])->toBe(1)
        ->and(collect($summary['metrics'])->firstWhere('key', 'add_on_revenue')['value'])->toBe(25.0)
        ->and($summary['revenue_by_plan'][0]['paid_amount'])->toBe(80.0);
});

it('requires platform permission for revenue metrics', function () {
    $tenantUser = User::factory()->for(Company::factory())->create();

    app(SaasRevenueMetricsService::class)->summary($tenantUser);
})->throws(AuthorizationException::class);
