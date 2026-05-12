<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollPeriodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'starts_on' => $this->starts_on?->toDateString(),
            'ends_on' => $this->ends_on?->toDateString(),
            'pay_date' => $this->pay_date?->toDateString(),
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'closed_at' => $this->closed_at?->toJSON(),
            'closed_by' => $this->closed_by,
            'metadata' => $this->metadata,
        ];
    }
}
