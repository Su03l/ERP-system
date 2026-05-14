<?php

namespace App\Actions;

use App\Enums\SubscriptionInvoiceStatus;
use App\Enums\SubscriptionStatus;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class MarkSubscriptionInvoicePaid
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  array{paid_amount?: int|float|string|null, metadata?: array<string, mixed>}  $data
     */
    public function handle(SubscriptionInvoice $invoice, array $data = [], ?User $actor = null): SubscriptionInvoice
    {
        return DB::transaction(function () use ($actor, $data, $invoice): SubscriptionInvoice {
            $oldValues = $invoice->attributesToArray();
            $paidAmount = $this->money(min($this->toCents($data['paid_amount'] ?? $invoice->total_amount), $this->toCents($invoice->total_amount)));
            $balanceDue = $this->money(max(0, $this->toCents($invoice->total_amount) - $this->toCents($paidAmount)));
            $status = ((float) $balanceDue) <= 0
                ? SubscriptionInvoiceStatus::Paid
                : SubscriptionInvoiceStatus::PartiallyPaid;

            $invoice->forceFill([
                'paid_amount' => $paidAmount,
                'balance_due' => $balanceDue,
                'status' => $status,
                'metadata' => array_replace($invoice->metadata ?? [], $data['metadata'] ?? []),
            ])->save();

            $subscription = $invoice->subscription;
            if ($status === SubscriptionInvoiceStatus::Paid && in_array($subscription->status, [SubscriptionStatus::Trialing, SubscriptionStatus::PastDue, SubscriptionStatus::Grace], true)) {
                $subscription->forceFill([
                    'status' => SubscriptionStatus::Active,
                    'cancelled_at' => null,
                    'grace_ends_at' => null,
                ])->save();
            }

            $this->auditLogger->log(
                action: 'subscription_invoice.paid',
                auditable: $invoice,
                oldValues: $oldValues,
                newValues: $invoice->refresh()->attributesToArray(),
                metadata: ['subscription_id' => $invoice->subscription_id],
                user: $actor,
                company: $invoice->company_id,
            );

            return $invoice->load('company', 'subscription.plan');
        });
    }

    private function toCents(int|float|string|null $amount): int
    {
        return (int) round(((float) ($amount ?? 0)) * 100);
    }

    private function money(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
