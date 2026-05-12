<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollRunResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'payroll_period_id' => $this->payroll_period_id,
            'run_number' => $this->run_number,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'total_employees' => $this->total_employees,
            'gross_amount' => $this->gross_amount,
            'total_allowances' => $this->total_allowances,
            'total_deductions' => $this->total_deductions,
            'net_amount' => $this->net_amount,
            'generated_by' => $this->generated_by,
            'generated_at' => $this->generated_at?->toJSON(),
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->toJSON(),
            'workflow_instance_id' => $this->workflow_instance_id,
            'metadata' => $this->metadata,
            'payroll_period' => PayrollPeriodResource::make($this->whenLoaded('payrollPeriod')),
            'items' => PayrollRunItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
