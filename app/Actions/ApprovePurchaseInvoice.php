<?php

namespace App\Actions;

use App\Enums\InvoiceStatus;
use App\Models\PurchaseInvoice;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ApprovePurchaseInvoice
{
    public function __construct(private readonly AuditLogger $auditLogger, private readonly TenantContext $tenantContext) {}

    public function handle(PurchaseInvoice $invoice, ?User $actor = null): PurchaseInvoice
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($invoice, $actor);
        Gate::forUser($actor)->authorize('purchase_invoices.approve');

        return DB::transaction(function () use ($actor, $invoice): PurchaseInvoice {
            if ($invoice->status !== InvoiceStatus::Draft) {
                throw ValidationException::withMessages(['status' => __('accounting.validation.purchase_invoices.approvable_status')]);
            }

            $oldValues = $invoice->attributesToArray();
            $invoice->forceFill(['status' => InvoiceStatus::Sent])->save();
            $this->auditLogger->log('purchase_invoice.approved', $invoice, $oldValues, $invoice->refresh()->attributesToArray(), user: $actor, company: $invoice->company_id);

            return $invoice;
        });
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to approve purchase invoices.');
        }

        return $actor;
    }

    private function ensureTenant(PurchaseInvoice $invoice, User $actor): void
    {
        if ($this->tenantContext->companyId() !== $invoice->company_id || $actor->company_id !== $invoice->company_id) {
            throw new AuthorizationException('Purchase invoice does not belong to the current company.');
        }
    }
}
