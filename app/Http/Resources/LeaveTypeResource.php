<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'code' => $this->code,
            'default_days_per_year' => $this->default_days_per_year,
            'is_paid' => $this->is_paid,
            'requires_approval' => $this->requires_approval,
            'allow_negative_balance' => $this->allow_negative_balance,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'description' => $this->description,
        ];
    }
}
