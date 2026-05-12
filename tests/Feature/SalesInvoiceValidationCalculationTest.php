<?php

use App\Http\Requests\StoreSalesInvoiceRequest;
use App\Http\Requests\UpdateSalesInvoiceRequest;
use App\Models\Company;
use App\Models\Customer;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Services\SalesInvoiceCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Route::post('/test/sales-invoices', fn (StoreSalesInvoiceRequest $request) => $request->validated());
    Route::patch('/test/sales-invoices/{salesInvoice}', fn (UpdateSalesInvoiceRequest $request, SalesInvoice $salesInvoice) => $request->validated());
});

function salesInvoicePayload(array $overrides = []): array
{
    return [
        'invoice_number' => $overrides['invoice_number'] ?? 'INV-2026-001',
        'invoice_date' => $overrides['invoice_date'] ?? '2026-05-12',
        'paid_amount' => $overrides['paid_amount'] ?? '50.00',
        'currency' => $overrides['currency'] ?? 'SAR',
        'lines' => $overrides['lines'] ?? [
            [
                'description_ar' => 'خدمة استشارية',
                'description_en' => 'Consulting service',
                'quantity' => '2',
                'unit_price' => '100.00',
                'discount_amount' => '10.00',
                'tax_rate' => '15',
            ],
            [
                'description_ar' => 'دعم فني',
                'description_en' => 'Support',
                'quantity' => '1',
                'unit_price' => '50.00',
                'discount_amount' => '0.00',
                'tax_rate' => '0',
            ],
        ],
    ] + $overrides;
}

it('calculates sales invoice totals from backend line data', function () {
    $result = app(SalesInvoiceCalculationService::class)->calculate(salesInvoicePayload()['lines'], '50.00');

    expect($result['subtotal'])->toBe('250.00')
        ->and($result['discount_amount'])->toBe('10.00')
        ->and($result['tax_amount'])->toBe('28.50')
        ->and($result['total_amount'])->toBe('268.50')
        ->and($result['paid_amount'])->toBe('50.00')
        ->and($result['balance_due'])->toBe('218.50')
        ->and($result['lines'][0]['tax_amount'])->toBe('28.50')
        ->and($result['lines'][0]['line_total'])->toBe('218.50');
});

it('validates store invoice payload tenant ownership and rejects frontend totals', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $customer = Customer::factory()->for($company)->create();

    $this->actingAs($actor)
        ->postJson('/test/sales-invoices', salesInvoicePayload([
            'customer_id' => $customer->id,
            'total_amount' => '1.00',
            'lines' => [
                [
                    'description_ar' => 'خدمة',
                    'quantity' => '1',
                    'unit_price' => '100.00',
                    'tax_rate' => '101',
                    'line_total' => '1.00',
                ],
            ],
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['total_amount', 'lines.0.tax_rate', 'lines.0.line_total']);
});

it('rejects customers from another company and duplicate invoice numbers', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $otherCustomer = Customer::factory()->for(Company::factory())->create();
    SalesInvoice::factory()->for($company)->create(['invoice_number' => 'INV-2026-001']);

    $this->actingAs($actor)
        ->postJson('/test/sales-invoices', salesInvoicePayload([
            'customer_id' => $otherCustomer->id,
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['customer_id', 'invoice_number']);
});

it('validates update invoice payload and ignores current invoice number', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $invoice = SalesInvoice::factory()->for($company)->create(['invoice_number' => 'INV-2026-001']);

    $this->actingAs($actor)
        ->patchJson("/test/sales-invoices/{$invoice->id}", [
            'invoice_number' => 'INV-2026-001',
            'lines' => [
                [
                    'description_ar' => 'خدمة محدثة',
                    'quantity' => '1',
                    'unit_price' => '200.00',
                    'tax_rate' => '15',
                ],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('invoice_number', 'INV-2026-001');
});

it('forbids updating sales invoices from another company', function () {
    $actor = User::factory()->for(Company::factory())->create();
    $invoice = SalesInvoice::factory()->for(Company::factory())->create();

    $this->actingAs($actor)
        ->patchJson("/test/sales-invoices/{$invoice->id}", ['notes_en' => 'Nope'])
        ->assertForbidden();
});
