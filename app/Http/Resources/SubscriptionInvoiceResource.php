<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionInvoiceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'subscription_id' => $this->subscription_id,
            'invoice_number' => $this->invoice_number,
            'invoice_date' => $this->invoice_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'discount_amount' => $this->discount_amount,
            'total_amount' => $this->total_amount,
            'paid_amount' => $this->paid_amount,
            'balance_due' => $this->balance_due,
            'currency' => $this->currency,
            'metadata' => $this->metadata,
            'company' => $this->when($this->relationLoaded('company') && $this->company !== null, fn (): array => [
                'id' => $this->company->id,
                'name' => $this->company->name,
            ]),
            'subscription' => $this->when($this->relationLoaded('subscription') && $this->subscription !== null, fn (): array => [
                'id' => $this->subscription->id,
                'plan_id' => $this->subscription->plan_id,
                'status' => $this->subscription->status?->value,
            ]),
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
        ];
    }
}
