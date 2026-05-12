<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalaryComponentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'code' => $this->code,
            'type' => $this->type?->value,
            'type_label' => $this->type?->label(),
            'calculation_type' => $this->calculation_type?->value,
            'calculation_type_label' => $this->calculation_type?->label(),
            'default_amount' => $this->default_amount,
            'default_percentage' => $this->default_percentage,
            'is_taxable' => $this->is_taxable,
            'is_recurring' => $this->is_recurring,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'metadata' => $this->metadata,
        ];
    }
}
