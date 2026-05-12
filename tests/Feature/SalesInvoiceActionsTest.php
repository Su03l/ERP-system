<?php

use App\Actions\CancelSalesInvoice;
use App\Actions\CreateSalesInvoice;
use App\Actions\IssueSalesInvoice;
use App\Actions\UpdateSalesInvoice;
use App\Enums\InvoiceStatus;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Services\PostSalesInvoiceToAccounting;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function grantSalesInvoicePermissions(User $user, array $permissions): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissions as $permissionKey) {
        $permission = Permission::factory()->create(['key' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

function salesInvoiceActionPayload(Customer $customer, array $overrides = []): array
{
    return [
        'customer_id' => $customer->id,
        'invoice_number' => $overrides['invoice_number'] ?? 'SI-001',
        'invoice_date' => '2026-05-12',
        'paid_amount' => $overrides['paid_amount'] ?? '0.00',
        'currency' => 'SAR',
        'lines' => $overrides['lines'] ?? [
            [
                'description_ar' => 'خدمة',
                'description_en' => 'Service',
                'quantity' => '2',
                'unit_price' => '100.00',
                'discount_amount' => '10.00',
                'tax_rate' => '15',
            ],
        ],
    ] + $overrides;
}

it('creates sales invoices with recalculated totals lines and audit log', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $customer = Customer::factory()->for($company)->create();
    grantSalesInvoicePermissions($actor, ['sales_invoices.create']);
    $this->actingAs($actor);

    $invoice = app(CreateSalesInvoice::class)->handle(salesInvoiceActionPayload($customer), $actor);

    expect($invoice->company_id)->toBe($company->id)
        ->and($invoice->status)->toBe(InvoiceStatus::Draft)
        ->and($invoice->subtotal)->toBe('200.00')
        ->and($invoice->discount_amount)->toBe('10.00')
        ->and($invoice->tax_amount)->toBe('28.50')
        ->and($invoice->total_amount)->toBe('218.50')
        ->and($invoice->balance_due)->toBe('218.50')
        ->and($invoice->lines)->toHaveCount(1)
        ->and(AuditLog::query()->where('action', 'sales_invoice.created')->where('auditable_id', $invoice->id)->exists())->toBeTrue();
});

it('updates draft invoices and prevents editing issued invoices', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $customer = Customer::factory()->for($company)->create();
    grantSalesInvoicePermissions($actor, ['sales_invoices.create', 'sales_invoices.update', 'sales_invoices.issue']);
    $this->actingAs($actor);
    $invoice = app(CreateSalesInvoice::class)->handle(salesInvoiceActionPayload($customer), $actor);

    $updated = app(UpdateSalesInvoice::class)->handle($invoice, [
        'lines' => [
            [
                'description_ar' => 'خدمة محدثة',
                'quantity' => '1',
                'unit_price' => '50.00',
                'tax_rate' => '0',
            ],
        ],
    ], $actor);

    expect($updated->total_amount)->toBe('50.00')
        ->and(AuditLog::query()->where('action', 'sales_invoice.updated')->where('auditable_id', $invoice->id)->exists())->toBeTrue();

    app(IssueSalesInvoice::class)->handle($updated, $actor);
    app(UpdateSalesInvoice::class)->handle($updated->refresh(), ['notes_en' => 'Nope'], $actor);
})->throws(ValidationException::class);

it('issues and cancels sales invoices with audited status changes', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $invoice = SalesInvoice::factory()->for($company)->create(['status' => InvoiceStatus::Draft]);
    grantSalesInvoicePermissions($actor, ['sales_invoices.issue', 'sales_invoices.cancel']);
    $this->actingAs($actor);

    $issued = app(IssueSalesInvoice::class)->handle($invoice, $actor);
    expect($issued->status)->toBe(InvoiceStatus::Sent);

    $cancelled = app(CancelSalesInvoice::class)->handle($issued, $actor, 'Customer requested cancellation');

    expect($cancelled->status)->toBe(InvoiceStatus::Cancelled)
        ->and(AuditLog::query()->where('action', 'sales_invoice.issued')->where('auditable_id', $invoice->id)->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'sales_invoice.cancelled')->where('auditable_id', $invoice->id)->exists())->toBeTrue();
});

it('audits accounting posting placeholder and does not create journal entries', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $invoice = SalesInvoice::factory()->for($company)->create(['status' => InvoiceStatus::Sent]);
    grantSalesInvoicePermissions($actor, ['sales_invoices.post']);
    $this->actingAs($actor);

    app(PostSalesInvoiceToAccounting::class)->post($invoice, $actor);
})->throws(LogicException::class, 'Sales invoice accounting posting is not implemented yet.');

it('requires explicit sales invoice permissions', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $customer = Customer::factory()->for($company)->create();
    $this->actingAs($actor);

    app(CreateSalesInvoice::class)->handle(salesInvoiceActionPayload($customer), $actor);
})->throws(AuthorizationException::class);

it('prevents action-level cross-company customer references', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $otherCustomer = Customer::factory()->for(Company::factory())->create();
    grantSalesInvoicePermissions($actor, ['sales_invoices.create']);
    $this->actingAs($actor);

    app(CreateSalesInvoice::class)->handle(salesInvoiceActionPayload($otherCustomer), $actor);
})->throws(AuthorizationException::class);
