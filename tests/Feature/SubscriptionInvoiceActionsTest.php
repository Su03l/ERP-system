<?php

use App\Actions\CancelSubscriptionInvoice;
use App\Actions\GenerateSubscriptionInvoice;
use App\Actions\MarkSubscriptionInvoicePaid;
use App\Enums\BillingCycle;
use App\Enums\SubscriptionInvoiceStatus;
use App\Enums\SubscriptionStatus;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Plan;
use App\Models\SaasSetting;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('generates subscription invoices from plan pricing and recalculates totals server side', function () {
    Carbon::setTestNow('2026-05-14 00:00:00');
    SaasSetting::factory()->create(['invoice_numbering_prefix' => 'NC-SUB']);
    $company = Company::factory()->create();
    $plan = Plan::factory()->create([
        'price_monthly' => '100.00',
        'price_yearly' => '1000.00',
        'currency' => 'SAR',
    ]);
    $subscription = CompanySubscription::factory()->for($company)->for($plan)->create([
        'status' => SubscriptionStatus::Active,
        'billing_cycle' => BillingCycle::Yearly,
    ]);
    $actor = User::factory()->for($company)->create();

    $invoice = app(GenerateSubscriptionInvoice::class)->handle($subscription, [
        'tax_amount' => '150.00',
        'discount_amount' => '50.00',
        'paid_amount' => '0.00',
        'total_amount' => '1.00',
        'due_date' => '2026-05-21',
    ], $actor);

    expect($invoice->invoice_number)->toBe('NC-SUB-000001')
        ->and($invoice->status)->toBe(SubscriptionInvoiceStatus::Open)
        ->and($invoice->subtotal)->toBe('1000.00')
        ->and($invoice->tax_amount)->toBe('150.00')
        ->and($invoice->discount_amount)->toBe('50.00')
        ->and($invoice->total_amount)->toBe('1100.00')
        ->and($invoice->balance_due)->toBe('1100.00')
        ->and($invoice->currency)->toBe('SAR')
        ->and(AuditLog::query()->where('action', 'subscription_invoice.generated')->where('auditable_id', $invoice->id)->exists())->toBeTrue();

    Carbon::setTestNow();
});

it('marks subscription invoices paid and activates grace subscriptions', function () {
    $company = Company::factory()->create();
    $subscription = CompanySubscription::factory()->for($company)->create(['status' => SubscriptionStatus::Grace]);
    $invoice = SubscriptionInvoice::factory()->forSubscription($subscription)->create([
        'status' => SubscriptionInvoiceStatus::Open,
        'total_amount' => '250.00',
        'paid_amount' => '0.00',
        'balance_due' => '250.00',
    ]);
    $actor = User::factory()->for($company)->create();

    $paid = app(MarkSubscriptionInvoicePaid::class)->handle($invoice, ['paid_amount' => '250.00'], $actor);

    expect($paid->status)->toBe(SubscriptionInvoiceStatus::Paid)
        ->and($paid->paid_amount)->toBe('250.00')
        ->and($paid->balance_due)->toBe('0.00')
        ->and($subscription->refresh()->status)->toBe(SubscriptionStatus::Active)
        ->and(AuditLog::query()->where('action', 'subscription_invoice.paid')->where('auditable_id', $invoice->id)->exists())->toBeTrue();
});

it('supports partial payment status for subscription invoices', function () {
    $invoice = SubscriptionInvoice::factory()->create([
        'status' => SubscriptionInvoiceStatus::Open,
        'total_amount' => '250.00',
        'paid_amount' => '0.00',
        'balance_due' => '250.00',
    ]);

    $paid = app(MarkSubscriptionInvoicePaid::class)->handle($invoice, ['paid_amount' => '100.00']);

    expect($paid->status)->toBe(SubscriptionInvoiceStatus::PartiallyPaid)
        ->and($paid->paid_amount)->toBe('100.00')
        ->and($paid->balance_due)->toBe('150.00');
});

it('cancels unpaid subscription invoices without deleting history', function () {
    $invoice = SubscriptionInvoice::factory()->create([
        'status' => SubscriptionInvoiceStatus::Open,
        'balance_due' => '100.00',
    ]);

    $cancelled = app(CancelSubscriptionInvoice::class)->handle($invoice, reason: 'manual correction');

    expect($cancelled->status)->toBe(SubscriptionInvoiceStatus::Cancelled)
        ->and($cancelled->balance_due)->toBe('0.00')
        ->and(SubscriptionInvoice::query()->whereKey($invoice)->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'subscription_invoice.cancelled')->where('auditable_id', $invoice->id)->exists())->toBeTrue();
});

it('prevents cancelling paid subscription invoices', function () {
    $invoice = SubscriptionInvoice::factory()->create(['status' => SubscriptionInvoiceStatus::Paid]);

    app(CancelSubscriptionInvoice::class)->handle($invoice);
})->throws(ValidationException::class);
