<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CrmLeadResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'assigned_user_id' => $this->assigned_user_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'company_name' => $this->company_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'source' => $this->source,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'expected_value' => $this->expected_value,
            'notes_ar' => $this->notes_ar,
            'notes_en' => $this->notes_en,
            'metadata' => $this->metadata,
            'assigned_user' => $this->whenLoaded('assignedUser', fn (): array => [
                'id' => $this->assignedUser->id,
                'name' => $this->assignedUser->name,
                'email' => $this->assignedUser->email,
            ]),
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
        ];
    }
}
