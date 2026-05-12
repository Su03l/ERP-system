<?php

use App\Enums\InvoiceStatus;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceLine;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates purchase invoice tables with required columns', function () {
    expect(Schema::hasColumns('purchase_invoices', [
        'company_id', 'vendor_id', 'invoice_number', 'vendor_invoice_number', 'invoice_date', 'due_date', 'status',
        'subtotal', 'tax_amount', 'discount_amount', 'total_amount', 'paid_amount', 'balance_due', 'currency',
        'notes_ar', 'notes_en', 'posted_journal_entry_id', 'metadata',
    ]))->toBeTrue()
        ->and(Schema::hasColumns('purchase_invoice_lines', [
            'company_id', 'purchase_invoice_id', 'description_ar', 'description_en', 'quantity', 'unit_price',
            'discount_amount', 'tax_rate', 'tax_amount', 'line_total', 'metadata',
        ]))->toBeTrue();
});

it('stores purchase invoices with relationships tenant scope and casts', function () {
    $company = Company::factory()->create();
    $vendor = Vendor::factory()->for($company)->create();
    $journalEntry = JournalEntry::factory()->for($company)->create();
    $invoice = PurchaseInvoice::factory()->for($company)->create([
        'vendor_id' => $vendor->id,
        'posted_journal_entry_id' => $journalEntry->id,
        'status' => InvoiceStatus::Sent,
        'subtotal' => 200,
        'tax_amount' => 30,
        'total_amount' => 230,
        'balance_due' => 230,
    ]);
    $line = PurchaseInvoiceLine::factory()->for($company)->create(['purchase_invoice_id' => $invoice->id, 'quantity' => 2]);

    expect($invoice->company->is($company))->toBeTrue()
        ->and($invoice->vendor->is($vendor))->toBeTrue()
        ->and($invoice->postedJournalEntry->is($journalEntry))->toBeTrue()
        ->and($invoice->lines()->whereKey($line)->exists())->toBeTrue()
        ->and($vendor->purchaseInvoices()->whereKey($invoice)->exists())->toBeTrue()
        ->and($company->purchaseInvoices()->whereKey($invoice)->exists())->toBeTrue()
        ->and($company->purchaseInvoiceLines()->whereKey($line)->exists())->toBeTrue()
        ->and($invoice->status)->toBe(InvoiceStatus::Sent)
        ->and($line->quantity)->toBe('2.000');
});

it('scopes purchase invoices and lines to current company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $invoice = PurchaseInvoice::factory()->for($company)->create();
    $line = PurchaseInvoiceLine::factory()->for($company)->create(['purchase_invoice_id' => $invoice->id]);
    PurchaseInvoice::factory()->for(Company::factory())->create();
    PurchaseInvoiceLine::factory()->for(Company::factory())->create();

    $this->actingAs($user);

    expect(PurchaseInvoice::query()->forCurrentCompany()->pluck('id')->all())->toBe([$invoice->id])
        ->and(PurchaseInvoiceLine::query()->forCurrentCompany()->pluck('id')->all())->toBe([$line->id]);
});
