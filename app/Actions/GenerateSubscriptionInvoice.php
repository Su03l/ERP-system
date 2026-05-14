<?php

namespace App\Actions;

use App\Enums\SubscriptionInvoiceStatus;
use App\Enums\SubscriptionStatus;
use App\Models\CompanySubscription;
use App\Models\SaasSetting;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\SubscriptionInvoiceCalculationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateSubscriptionInvoice
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly SubscriptionInvoiceCalculationService $calculationService,
    ) {}

    /**
     * @param  array{invoice_number?: string, invoice_date?: Carbon|string|null, due_date?: Carbon|string|null, tax_amount?: int|float|string|null, discount_amount?: int|float|string|null, paid_amount?: int|float|string|null, currency?: string|null, metadata?: array<string, mixed>}  $data
     */
    public function handle(CompanySubscription $subscription, array $data = [], ?User $actor = null): SubscriptionInvoice
    {
        return DB::transaction(function () use ($actor, $data, $subscription): SubscriptionInvoice {
            $subscription->loadMissing('plan');
            $calculation = $this->calculationService->calculate($subscription, $data);
            $status = ((float) $calculation['balance_due']) <= 0
                ? SubscriptionInvoiceStatus::Paid
                : SubscriptionInvoiceStatus::Open;

            $invoice = SubscriptionInvoice::create([
                'company_id' => $subscription->company_id,
                'subscription_id' => $subscription->id,
                'invoice_number' => $data['invoice_number'] ?? $this->nextInvoiceNumber(),
                'invoice_date' => isset($data['invoice_date']) ? Carbon::parse($data['invoice_date'])->toDateString() : now()->toDateString(),
                'due_date' => isset($data['due_date']) ? Carbon::parse($data['due_date'])->toDateString() : null,
                'status' => $status,
                'currency' => $data['currency'] ?? $subscription->plan->currency,
                'metadata' => $data['metadata'] ?? [],
                ...$calculation,
            ]);

            if ($status === SubscriptionInvoiceStatus::Paid && in_array($subscription->status, [SubscriptionStatus::Trialing, SubscriptionStatus::PastDue, SubscriptionStatus::Grace], true)) {
                $subscription->forceFill([
                    'status' => SubscriptionStatus::Active,
                    'cancelled_at' => null,
                    'grace_ends_at' => null,
                ])->save();
            }

            $this->auditLogger->log(
                action: 'subscription_invoice.generated',
                auditable: $invoice,
                newValues: $invoice->attributesToArray(),
                metadata: ['subscription_id' => $subscription->id],
                user: $actor,
                company: $invoice->company_id,
            );

            return $invoice->refresh()->load('company', 'subscription.plan');
        });
    }

    private function nextInvoiceNumber(): string
    {
        $prefix = SaasSetting::query()->latest('id')->value('invoice_numbering_prefix') ?? 'SAAS-INV';
        $next = SubscriptionInvoice::query()->count() + 1;

        return sprintf('%s-%06d', $prefix, $next);
    }
}
