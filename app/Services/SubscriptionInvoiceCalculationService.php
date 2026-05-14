<?php

namespace App\Services;

use App\Enums\SubscriptionBillingCycle;
use App\Models\CompanySubscription;

class SubscriptionInvoiceCalculationService
{
    /**
     * @param  array{tax_amount?: int|float|string|null, discount_amount?: int|float|string|null, paid_amount?: int|float|string|null}  $data
     * @return array{subtotal: string, tax_amount: string, discount_amount: string, total_amount: string, paid_amount: string, balance_due: string}
     */
    public function calculate(CompanySubscription $subscription, array $data = []): array
    {
        $subtotal = $this->subscriptionPriceCents($subscription);
        $taxAmount = $this->toCents($data['tax_amount'] ?? 0);
        $discountAmount = min($this->toCents($data['discount_amount'] ?? 0), $subtotal + $taxAmount);
        $totalAmount = max(0, $subtotal + $taxAmount - $discountAmount);
        $paidAmount = min($this->toCents($data['paid_amount'] ?? 0), $totalAmount);

        return [
            'subtotal' => $this->money($subtotal),
            'tax_amount' => $this->money($taxAmount),
            'discount_amount' => $this->money($discountAmount),
            'total_amount' => $this->money($totalAmount),
            'paid_amount' => $this->money($paidAmount),
            'balance_due' => $this->money($totalAmount - $paidAmount),
        ];
    }

    private function subscriptionPriceCents(CompanySubscription $subscription): int
    {
        $plan = $subscription->plan;

        if ($subscription->billing_cycle === SubscriptionBillingCycle::Yearly) {
            return $this->toCents($plan->price_yearly ?? ((float) $plan->price_monthly * 12));
        }

        return $this->toCents($plan->price_monthly);
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
