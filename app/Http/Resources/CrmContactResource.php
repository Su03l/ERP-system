<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CrmContactResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'customer_id' => $this->customer_id,
            'lead_id' => $this->lead_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'email' => $this->email,
            'phone' => $this->phone,
            'position' => $this->position,
            'notes_ar' => $this->notes_ar,
            'notes_en' => $this->notes_en,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'metadata' => $this->metadata,
            'customer' => $this->when($this->relationLoaded('customer') && $this->customer !== null, fn (): array => [
                'id' => $this->customer->id,
                'name_ar' => $this->customer->name_ar,
                'name_en' => $this->customer->name_en,
            ]),
            'lead' => $this->when($this->relationLoaded('lead') && $this->lead !== null, fn (): CrmLeadResource => CrmLeadResource::make($this->lead)),
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
        ];
    }
}
