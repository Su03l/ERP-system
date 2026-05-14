<?php

namespace App\Actions;

use App\Models\SubscriptionInvoice;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class UpdateSubscriptionInvoice
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /** @param array<string, mixed> $data */
    public function handle(SubscriptionInvoice $invoice, array $data, ?User $actor = null): SubscriptionInvoice
    {
        return DB::transaction(function () use ($actor, $data, $invoice): SubscriptionInvoice {
            $oldValues = $invoice->attributesToArray();
            $invoice->update($data);

            $this->auditLogger->log(
                action: 'subscription_invoice.updated',
                auditable: $invoice,
                oldValues: $oldValues,
                newValues: $invoice->refresh()->attributesToArray(),
                user: $actor,
                company: $invoice->company_id,
            );

            return $invoice->load(['company', 'subscription.plan']);
        });
    }
}
