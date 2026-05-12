<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeSalaryPackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'employee_id' => $this->employee_id,
            'basic_salary' => $this->basic_salary,
            'housing_allowance' => $this->housing_allowance,
            'transportation_allowance' => $this->transportation_allowance,
            'effective_from' => $this->effective_from?->toDateString(),
            'effective_to' => $this->effective_to?->toDateString(),
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'metadata' => $this->metadata,
            'employee' => EmployeeResource::make($this->whenLoaded('employee')),
            'items' => $this->whenLoaded('items'),
        ];
    }
}
