<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\SalesInvoice;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use LogicException;

class PostSalesInvoiceToAccounting
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function post(SalesInvoice $invoice, ?User $actor = null): never
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User || $actor->company_id !== $invoice->company_id || ! $actor->hasPermission('sales_invoices.post', $invoice->company_id)) {
            throw new AuthorizationException('You are not authorized to post sales invoices to accounting.');
        }

        if (! in_array($invoice->status, [InvoiceStatus::Sent, InvoiceStatus::PartiallyPaid, InvoiceStatus::Paid], true)) {
            throw new LogicException('Only issued sales invoices can be posted to accounting.');
        }

        $this->auditLogger->log(
            action: 'sales_invoice.accounting_posting_requested',
            auditable: $invoice,
            metadata: ['status' => 'posting_placeholder'],
            user: $actor,
            company: $invoice->company_id,
        );

        throw new LogicException('Sales invoice accounting posting is not implemented yet.');
    }
}
