<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyAddOnResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'add_on_id' => $this->add_on_id,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'starts_at' => $this->starts_at?->toJSON(),
            'ends_at' => $this->ends_at?->toJSON(),
            'metadata' => $this->metadata,
            'company' => $this->when($this->relationLoaded('company') && $this->company !== null, fn (): array => [
                'id' => $this->company->id,
                'name' => $this->company->name,
            ]),
            'add_on' => $this->when($this->relationLoaded('addOn') && $this->addOn !== null, fn (): array => [
                'id' => $this->addOn->id,
                'name_ar' => $this->addOn->name_ar,
                'name_en' => $this->addOn->name_en,
                'code' => $this->addOn->code,
                'feature_key' => $this->addOn->feature_key,
            ]),
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
        ];
    }
}
