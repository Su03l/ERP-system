<?php

use App\Enums\SubscriptionInvoiceStatus;
use App\Enums\SubscriptionStatus;
use App\Models\AddOn;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Plan;
use App\Models\SubscriptionInvoice;
use App\Models\UsageSnapshot;
use App\Models\User;
use App\Services\SaasExportService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('prepares platform export arrays for SaaS billing and marketplace records', function () {
    $actor = User::factory()->create(['company_id' => null]);
    $plan = Plan::factory()->create(['code' => 'PRO']);
    $subscription = CompanySubscription::factory()->for($plan, 'plan')->create([
        'status' => SubscriptionStatus::Active,
    ]);
    SubscriptionInvoice::query()->create([
        'company_id' => $subscription->company_id,
        'subscription_id' => $subscription->id,
        'invoice_number' => 'SUB-001',
        'invoice_date' => now(),
        'status' => SubscriptionInvoiceStatus::Draft,
        'subtotal' => 100,
        'tax_amount' => 0,
        'discount_amount' => 0,
        'total_amount' => 100,
        'paid_amount' => 0,
        'balance_due' => 100,
        'currency' => 'SAR',
    ]);
    AddOn::factory()->create(['code' => 'ADD-001']);
    UsageSnapshot::factory()->for($subscription->company)->create();

    $service = app(SaasExportService::class);

    expect($service->plans($actor))->toHaveCount(1)
        ->and($service->subscriptions($actor))->toHaveCount(1)
        ->and($service->subscriptionInvoices($actor))->toHaveCount(1)
        ->and($service->addOns($actor))->toHaveCount(1)
        ->and($service->usageReports($actor))->toHaveCount(1);
});

it('blocks tenant users from platform exports', function () {
    $tenantUser = User::factory()->for(Company::factory())->create();

    app(SaasExportService::class)->plans($tenantUser);
})->throws(AuthorizationException::class);
