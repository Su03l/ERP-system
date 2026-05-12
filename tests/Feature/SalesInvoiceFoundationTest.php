<?php

use App\Enums\InvoiceStatus;
use App\Models\Company;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates sales invoice tables with required columns', function () {
    expect(Schema::hasColumns('sales_invoices', [
        'id',
        'company_id',
        'customer_id',
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
        'notes_ar',
        'notes_en',
        'posted_journal_entry_id',
        'metadata',
        'created_at',
        'updated_at',
    ]))->toBeTrue()
        ->and(Schema::hasColumns('sales_invoice_lines', [
            'id',
            'company_id',
            'sales_invoice_id',
            'description_ar',
            'description_en',
            'quantity',
            'unit_price',
            'discount_amount',
            'tax_rate',
            'tax_amount',
            'line_total',
            'metadata',
            'created_at',
            'updated_at',
        ]))->toBeTrue();
});

it('stores sales invoices with relationships tenant scope and casts', function () {
    $company = Company::factory()->create();
    $customer = Customer::factory()->for($company)->create();
    $journalEntry = JournalEntry::factory()->for($company)->create();

    $invoice = SalesInvoice::factory()->for($company)->create([
        'customer_id' => $customer->id,
        'posted_journal_entry_id' => $journalEntry->id,
        'status' => InvoiceStatus::Sent,
        'subtotal' => 200,
        'tax_amount' => 30,
        'total_amount' => 230,
        'balance_due' => 230,
        'metadata' => ['channel' => 'sales'],
    ]);

    $line = SalesInvoiceLine::factory()->for($company)->create([
        'sales_invoice_id' => $invoice->id,
        'quantity' => 2,
        'unit_price' => 100,
        'tax_amount' => 30,
        'line_total' => 230,
    ]);

    expect($invoice->company->is($company))->toBeTrue()
        ->and($invoice->customer->is($customer))->toBeTrue()
        ->and($invoice->postedJournalEntry->is($journalEntry))->toBeTrue()
        ->and($invoice->lines()->whereKey($line)->exists())->toBeTrue()
        ->and($customer->salesInvoices()->whereKey($invoice)->exists())->toBeTrue()
        ->and($company->salesInvoices()->whereKey($invoice)->exists())->toBeTrue()
        ->and($company->salesInvoiceLines()->whereKey($line)->exists())->toBeTrue()
        ->and($invoice->status)->toBe(InvoiceStatus::Sent)
        ->and($invoice->subtotal)->toBe('200.00')
        ->and($line->quantity)->toBe('2.000')
        ->and($line->line_total)->toBe('230.00');
});

it('scopes sales invoices and lines to current company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $invoice = SalesInvoice::factory()->for($company)->create();
    $line = SalesInvoiceLine::factory()->for($company)->create(['sales_invoice_id' => $invoice->id]);
    SalesInvoice::factory()->for($otherCompany)->create();
    SalesInvoiceLine::factory()->for($otherCompany)->create();

    $this->actingAs($user);

    expect(SalesInvoice::query()->forCurrentCompany()->pluck('id')->all())->toBe([$invoice->id])
        ->and(SalesInvoiceLine::query()->forCurrentCompany()->pluck('id')->all())->toBe([$line->id]);
});
