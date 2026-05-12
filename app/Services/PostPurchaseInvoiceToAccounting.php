<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\PurchaseInvoice;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use LogicException;

class PostPurchaseInvoiceToAccounting
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function post(PurchaseInvoice $invoice, ?User $actor = null): never
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User || $actor->company_id !== $invoice->company_id || ! $actor->hasPermission('purchase_invoices.post', $invoice->company_id)) {
            throw new AuthorizationException('You are not authorized to post purchase invoices to accounting.');
        }

        if (! in_array($invoice->status, [InvoiceStatus::Sent, InvoiceStatus::PartiallyPaid, InvoiceStatus::Paid], true)) {
            throw new LogicException('Only approved purchase invoices can be posted to accounting.');
        }

        $this->auditLogger->log('purchase_invoice.accounting_posting_requested', $invoice, metadata: ['status' => 'posting_placeholder'], user: $actor, company: $invoice->company_id);

        throw new LogicException('Purchase invoice accounting posting is not implemented yet.');
    }
}
