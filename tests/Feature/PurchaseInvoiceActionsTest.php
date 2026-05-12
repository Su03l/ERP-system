<?php

use App\Actions\ApprovePurchaseInvoice;
use App\Actions\CancelPurchaseInvoice;
use App\Actions\CreatePurchaseInvoice;
use App\Actions\UpdatePurchaseInvoice;
use App\Enums\InvoiceStatus;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Permission;
use App\Models\PurchaseInvoice;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Services\PostPurchaseInvoiceToAccounting;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function grantPurchaseInvoicePermissions(User $user, array $permissions): void
{
    $role = Role::factory()->for($user->company)->create();
    foreach ($permissions as $permissionKey) {
        $role->permissions()->attach(Permission::factory()->create(['key' => $permissionKey]));
    }
    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

function purchaseInvoiceActionPayload(Vendor $vendor): array
{
    return [
        'vendor_id' => $vendor->id,
        'invoice_number' => 'PI-001',
        'invoice_date' => '2026-05-12',
        'lines' => [['description_ar' => 'مواد', 'quantity' => '2', 'unit_price' => '100.00', 'discount_amount' => '10.00', 'tax_rate' => '15']],
    ];
}

it('creates purchase invoices with recalculated totals and audit log', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $vendor = Vendor::factory()->for($company)->create();
    grantPurchaseInvoicePermissions($actor, ['purchase_invoices.create']);
    $this->actingAs($actor);

    $invoice = app(CreatePurchaseInvoice::class)->handle(purchaseInvoiceActionPayload($vendor), $actor);

    expect($invoice->total_amount)->toBe('218.50')
        ->and($invoice->balance_due)->toBe('218.50')
        ->and($invoice->lines)->toHaveCount(1)
        ->and(AuditLog::query()->where('action', 'purchase_invoice.created')->where('auditable_id', $invoice->id)->exists())->toBeTrue();
});

it('updates draft invoices and prevents editing approved invoices', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $vendor = Vendor::factory()->for($company)->create();
    grantPurchaseInvoicePermissions($actor, ['purchase_invoices.create', 'purchase_invoices.update', 'purchase_invoices.approve']);
    $this->actingAs($actor);
    $invoice = app(CreatePurchaseInvoice::class)->handle(purchaseInvoiceActionPayload($vendor), $actor);

    $updated = app(UpdatePurchaseInvoice::class)->handle($invoice, ['lines' => [['description_ar' => 'محدث', 'quantity' => '1', 'unit_price' => '50', 'tax_rate' => '0']]], $actor);
    expect($updated->total_amount)->toBe('50.00');

    app(ApprovePurchaseInvoice::class)->handle($updated, $actor);
    app(UpdatePurchaseInvoice::class)->handle($updated->refresh(), ['notes_en' => 'Nope'], $actor);
})->throws(ValidationException::class);

it('approves cancels and audits purchase invoices', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $invoice = PurchaseInvoice::factory()->for($company)->create(['status' => InvoiceStatus::Draft]);
    grantPurchaseInvoicePermissions($actor, ['purchase_invoices.approve', 'purchase_invoices.cancel']);
    $this->actingAs($actor);

    $approved = app(ApprovePurchaseInvoice::class)->handle($invoice, $actor);
    expect($approved->status)->toBe(InvoiceStatus::Sent);

    $cancelled = app(CancelPurchaseInvoice::class)->handle($approved, $actor, 'Duplicate invoice');
    expect($cancelled->status)->toBe(InvoiceStatus::Cancelled)
        ->and(AuditLog::query()->where('action', 'purchase_invoice.approved')->where('auditable_id', $invoice->id)->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'purchase_invoice.cancelled')->where('auditable_id', $invoice->id)->exists())->toBeTrue();
});

it('audits purchase posting placeholder without creating journal entries', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $invoice = PurchaseInvoice::factory()->for($company)->create(['status' => InvoiceStatus::Sent]);
    grantPurchaseInvoicePermissions($actor, ['purchase_invoices.post']);
    $this->actingAs($actor);

    app(PostPurchaseInvoiceToAccounting::class)->post($invoice, $actor);
})->throws(LogicException::class, 'Purchase invoice accounting posting is not implemented yet.');

it('requires explicit purchase invoice permissions', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $vendor = Vendor::factory()->for($company)->create();
    $this->actingAs($actor);

    app(CreatePurchaseInvoice::class)->handle(purchaseInvoiceActionPayload($vendor), $actor);
})->throws(AuthorizationException::class);
