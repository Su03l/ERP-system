<?php

namespace App\Actions;

use App\Enums\SubscriptionInvoiceStatus;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelSubscriptionInvoice
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(SubscriptionInvoice $invoice, ?User $actor = null, ?string $reason = null): SubscriptionInvoice
    {
        return DB::transaction(function () use ($actor, $invoice, $reason): SubscriptionInvoice {
            if ($invoice->status === SubscriptionInvoiceStatus::Paid) {
                throw ValidationException::withMessages([
                    'status' => __('saas.subscription_invoices.cannot_cancel_paid'),
                ]);
            }

            $oldValues = $invoice->attributesToArray();
            $invoice->forceFill([
                'status' => SubscriptionInvoiceStatus::Cancelled,
                'balance_due' => 0,
            ])->save();

            $this->auditLogger->log(
                action: 'subscription_invoice.cancelled',
                auditable: $invoice,
                oldValues: $oldValues,
                newValues: $invoice->refresh()->attributesToArray(),
                metadata: ['reason' => $reason],
                user: $actor,
                company: $invoice->company_id,
            );

            return $invoice->load('company', 'subscription.plan');
        });
    }
}
