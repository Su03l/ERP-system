<?php

use App\Http\Requests\StorePurchaseInvoiceRequest;
use App\Http\Requests\UpdatePurchaseInvoiceRequest;
use App\Models\Company;
use App\Models\PurchaseInvoice;
use App\Models\User;
use App\Models\Vendor;
use App\Services\PurchaseInvoiceCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Route::post('/test/purchase-invoices', fn (StorePurchaseInvoiceRequest $request) => $request->validated());
    Route::patch('/test/purchase-invoices/{purchaseInvoice}', fn (UpdatePurchaseInvoiceRequest $request, PurchaseInvoice $purchaseInvoice) => $request->validated());
});

function purchaseInvoicePayload(array $overrides = []): array
{
    return [
        'invoice_number' => $overrides['invoice_number'] ?? 'PINV-2026-001',
        'vendor_invoice_number' => $overrides['vendor_invoice_number'] ?? 'VEND-INV-1',
        'invoice_date' => '2026-05-12',
        'paid_amount' => '25.00',
        'currency' => 'SAR',
        'lines' => $overrides['lines'] ?? [
            ['description_ar' => 'مواد', 'quantity' => '2', 'unit_price' => '100.00', 'discount_amount' => '10.00', 'tax_rate' => '15'],
        ],
    ] + $overrides;
}

it('calculates purchase invoice totals from backend line data', function () {
    $result = app(PurchaseInvoiceCalculationService::class)->calculate(purchaseInvoicePayload()['lines'], '25.00');

    expect($result['subtotal'])->toBe('200.00')
        ->and($result['discount_amount'])->toBe('10.00')
        ->and($result['tax_amount'])->toBe('28.50')
        ->and($result['total_amount'])->toBe('218.50')
        ->and($result['balance_due'])->toBe('193.50');
});

it('validates vendor tenant ownership and rejects frontend totals', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $vendor = Vendor::factory()->for($company)->create();

    $this->actingAs($actor)
        ->postJson('/test/purchase-invoices', purchaseInvoicePayload([
            'vendor_id' => $vendor->id,
            'total_amount' => '1.00',
            'lines' => [['description_ar' => 'مواد', 'quantity' => '1', 'unit_price' => '100', 'tax_rate' => '101', 'line_total' => '1']],
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['total_amount', 'lines.0.tax_rate', 'lines.0.line_total']);
});

it('rejects vendors from another company and duplicate invoice numbers', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $otherVendor = Vendor::factory()->for(Company::factory())->create();
    PurchaseInvoice::factory()->for($company)->create(['invoice_number' => 'PINV-2026-001', 'vendor_invoice_number' => 'VEND-INV-1']);

    $this->actingAs($actor)
        ->postJson('/test/purchase-invoices', purchaseInvoicePayload(['vendor_id' => $otherVendor->id]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['vendor_id', 'invoice_number', 'vendor_invoice_number']);
});

it('validates updates and forbids cross-company invoices', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $invoice = PurchaseInvoice::factory()->for($company)->create(['invoice_number' => 'PINV-2026-001']);
    $otherInvoice = PurchaseInvoice::factory()->for(Company::factory())->create();

    $this->actingAs($actor)
        ->patchJson("/test/purchase-invoices/{$invoice->id}", ['invoice_number' => 'PINV-2026-001'])
        ->assertSuccessful();

    $this->actingAs($actor)
        ->patchJson("/test/purchase-invoices/{$otherInvoice->id}", ['notes_en' => 'Nope'])
        ->assertForbidden();
});
