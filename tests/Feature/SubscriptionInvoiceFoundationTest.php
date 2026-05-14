<?php

use App\Enums\SubscriptionInvoiceStatus;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates the subscription invoices schema', function () {
    expect(Schema::hasColumns('subscription_invoices', [
        'id',
        'company_id',
        'subscription_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'balance_due',
        'currency',
        'metadata',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

it('stores platform billing invoices separate from tenant sales invoices', function () {
    $company = Company::factory()->create();
    $subscription = CompanySubscription::factory()->for($company)->create();

    $invoice = SubscriptionInvoice::factory()->forSubscription($subscription)->create([
        'invoice_number' => 'SUB-2026-0001',
        'invoice_date' => '2026-05-14',
        'due_date' => '2026-05-21',
        'status' => SubscriptionInvoiceStatus::Open,
        'subtotal' => '100.25',
        'tax_amount' => '15.04',
        'discount_amount' => '5.00',
        'total_amount' => '110.29',
        'paid_amount' => '10.29',
        'balance_due' => '100.00',
        'currency' => 'SAR',
        'metadata' => ['platform_billing' => true],
    ]);

    expect($invoice->company->is($company))->toBeTrue()
        ->and($invoice->subscription->is($subscription))->toBeTrue()
        ->and($company->subscriptionInvoices()->whereKey($invoice)->exists())->toBeTrue()
        ->and($subscription->invoices()->whereKey($invoice)->exists())->toBeTrue()
        ->and($invoice->status)->toBe(SubscriptionInvoiceStatus::Open)
        ->and($invoice->subtotal)->toBe('100.25')
        ->and($invoice->tax_amount)->toBe('15.04')
        ->and($invoice->discount_amount)->toBe('5.00')
        ->and($invoice->total_amount)->toBe('110.29')
        ->and($invoice->paid_amount)->toBe('10.29')
        ->and($invoice->balance_due)->toBe('100.00')
        ->and($invoice->metadata)->toBe(['platform_billing' => true]);
});

it('scopes subscription invoices to the current company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $invoice = SubscriptionInvoice::factory()->forSubscription(
        CompanySubscription::factory()->for($company)->create(),
    )->create();
    SubscriptionInvoice::factory()->forSubscription(
        CompanySubscription::factory()->for($otherCompany)->create(),
    )->create();

    $this->actingAs($user);

    expect(SubscriptionInvoice::query()->forCurrentCompany()->pluck('id')->all())->toBe([$invoice->id]);
});

it('provides localized subscription invoice status labels', function () {
    app()->setLocale('ar');

    expect(SubscriptionInvoiceStatus::Open->label())->toBe('مفتوحة')
        ->and(SubscriptionInvoiceStatus::PartiallyPaid->label())->toBe('مدفوعة جزئياً')
        ->and(SubscriptionInvoiceStatus::values())->toContain('paid');
});
