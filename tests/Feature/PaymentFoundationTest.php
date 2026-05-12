<?php

use App\Enums\PaymentDirection;
use App\Enums\PaymentStatus;
use App\Models\Company;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates the payments table with required columns', function () {
    expect(Schema::hasColumns('payments', [
        'company_id', 'payable_type', 'payable_id', 'customer_id', 'vendor_id', 'payment_number', 'payment_date',
        'direction', 'method', 'amount', 'currency', 'reference', 'status', 'notes_ar', 'notes_en',
        'posted_journal_entry_id', 'metadata',
    ]))->toBeTrue();
});

it('stores payments with tenant scope polymorphic payable and casts', function () {
    $company = Company::factory()->create();
    $customer = Customer::factory()->for($company)->create();
    $vendor = Vendor::factory()->for($company)->create();
    $invoice = SalesInvoice::factory()->for($company)->create(['customer_id' => $customer->id]);
    $journalEntry = JournalEntry::factory()->for($company)->create();
    $payment = Payment::factory()->for($company)->create([
        'payable_type' => $invoice->getMorphClass(),
        'payable_id' => $invoice->id,
        'customer_id' => $customer->id,
        'vendor_id' => $vendor->id,
        'posted_journal_entry_id' => $journalEntry->id,
        'direction' => PaymentDirection::Incoming,
        'status' => PaymentStatus::Completed,
        'amount' => 125,
    ]);

    expect($payment->company->is($company))->toBeTrue()
        ->and($payment->payable->is($invoice))->toBeTrue()
        ->and($payment->customer->is($customer))->toBeTrue()
        ->and($payment->vendor->is($vendor))->toBeTrue()
        ->and($payment->postedJournalEntry->is($journalEntry))->toBeTrue()
        ->and($company->payments()->whereKey($payment)->exists())->toBeTrue()
        ->and($customer->payments()->whereKey($payment)->exists())->toBeTrue()
        ->and($vendor->payments()->whereKey($payment)->exists())->toBeTrue()
        ->and($payment->direction)->toBe(PaymentDirection::Incoming)
        ->and($payment->status)->toBe(PaymentStatus::Completed)
        ->and($payment->amount)->toBe('125.00');
});

it('scopes payments to the current company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $payment = Payment::factory()->for($company)->create();
    Payment::factory()->for(Company::factory())->create();

    $this->actingAs($user);

    expect(Payment::query()->forCurrentCompany()->pluck('id')->all())->toBe([$payment->id]);
});

it('provides payment direction labels', function () {
    app()->setLocale('en');

    expect(PaymentDirection::Incoming->label())->toBe('Incoming')
        ->and(PaymentDirection::values())->toBe(['incoming', 'outgoing']);
});
