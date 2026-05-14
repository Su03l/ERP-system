<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanySubscriptionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'plan_id' => $this->plan_id,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'billing_cycle' => $this->billing_cycle?->value,
            'billing_cycle_label' => $this->billing_cycle?->label(),
            'starts_at' => $this->starts_at?->toJSON(),
            'ends_at' => $this->ends_at?->toJSON(),
            'trial_ends_at' => $this->trial_ends_at?->toJSON(),
            'cancelled_at' => $this->cancelled_at?->toJSON(),
            'grace_ends_at' => $this->grace_ends_at?->toJSON(),
            'metadata' => $this->metadata,
            'company' => $this->when($this->relationLoaded('company') && $this->company !== null, fn (): array => [
                'id' => $this->company->id,
                'name' => $this->company->name,
            ]),
            'plan' => $this->when($this->relationLoaded('plan') && $this->plan !== null, fn (): array => [
                'id' => $this->plan->id,
                'name_ar' => $this->plan->name_ar,
                'name_en' => $this->plan->name_en,
                'code' => $this->plan->code,
            ]),
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
        ];
    }
}
