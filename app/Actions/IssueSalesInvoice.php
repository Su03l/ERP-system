<?php

namespace App\Actions;

use App\Enums\InvoiceStatus;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class IssueSalesInvoice
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    public function handle(SalesInvoice $invoice, ?User $actor = null): SalesInvoice
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($invoice, $actor);
        Gate::forUser($actor)->authorize('sales_invoices.issue');

        return DB::transaction(function () use ($actor, $invoice): SalesInvoice {
            if ($invoice->status !== InvoiceStatus::Draft) {
                throw ValidationException::withMessages([
                    'status' => __('accounting.validation.sales_invoices.issuable_status'),
                ]);
            }

            $oldValues = $invoice->attributesToArray();
            $invoice->forceFill(['status' => InvoiceStatus::Sent])->save();

            $this->auditLogger->log('sales_invoice.issued', $invoice, $oldValues, $invoice->refresh()->attributesToArray(), user: $actor, company: $invoice->company_id);

            return $invoice;
        });
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to issue sales invoices.');
        }

        return $actor;
    }

    private function ensureTenant(SalesInvoice $invoice, User $actor): void
    {
        if ($this->tenantContext->companyId() !== $invoice->company_id || $actor->company_id !== $invoice->company_id) {
            throw new AuthorizationException('Sales invoice does not belong to the current company.');
        }
    }
}
