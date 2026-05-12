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

class CancelPurchaseInvoice
{
    public function __construct(private readonly AuditLogger $auditLogger, private readonly TenantContext $tenantContext) {}

    public function handle(PurchaseInvoice $invoice, ?User $actor = null, ?string $reason = null): PurchaseInvoice
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($invoice, $actor);
        Gate::forUser($actor)->authorize('purchase_invoices.cancel');

        return DB::transaction(function () use ($actor, $invoice, $reason): PurchaseInvoice {
            if ($invoice->posted_journal_entry_id !== null || in_array($invoice->status, [InvoiceStatus::Paid, InvoiceStatus::Cancelled, InvoiceStatus::Voided], true)) {
                throw ValidationException::withMessages(['status' => __('accounting.validation.purchase_invoices.cancelable_status')]);
            }

            $oldValues = $invoice->attributesToArray();
            $invoice->forceFill(['status' => InvoiceStatus::Cancelled])->save();
            $this->auditLogger->log('purchase_invoice.cancelled', $invoice, $oldValues, $invoice->refresh()->attributesToArray(), ['reason' => $reason], $actor, $invoice->company_id);

            return $invoice;
        });
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to cancel purchase invoices.');
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
