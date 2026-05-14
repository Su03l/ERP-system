<?php

use App\Enums\SubscriptionInvoiceStatus;
use App\Enums\SubscriptionStatus;
use App\Models\CompanySubscription;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use App\Services\SaasExportService;
use App\Services\SaasRevenueMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('keeps SaaS review surfaces platform-only and export-ready', function () {
    $actor = User::factory()->create(['company_id' => null]);
    $subscription = CompanySubscription::factory()->create([
        'status' => SubscriptionStatus::Active,
        'ends_at' => now()->addMonth(),
    ]);
    SubscriptionInvoice::query()->create([
        'company_id' => $subscription->company_id,
        'subscription_id' => $subscription->id,
        'invoice_number' => 'SUB-REVIEW',
        'status' => SubscriptionInvoiceStatus::Paid,
        'invoice_date' => now(),
        'subtotal' => 100,
        'tax_amount' => 0,
        'discount_amount' => 0,
        'total_amount' => 100,
        'paid_amount' => 100,
        'balance_due' => 0,
        'currency' => 'SAR',
    ]);

    $metrics = app(SaasRevenueMetricsService::class)->summary($actor);
    $exports = app(SaasExportService::class)->revenueMetrics($actor);

    expect($metrics['metrics'])->not->toBeEmpty()
        ->and($exports['metrics'])->not->toBeEmpty()
        ->and($exports['filters'])->toHaveKeys(['from', 'until']);
});
