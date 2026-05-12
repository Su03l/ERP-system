<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollRunItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'payroll_run_id' => $this->payroll_run_id,
            'employee_id' => $this->employee_id,
            'basic_salary' => $this->basic_salary,
            'gross_salary' => $this->gross_salary,
            'total_allowances' => $this->total_allowances,
            'total_deductions' => $this->total_deductions,
            'net_salary' => $this->net_salary,
            'attendance_deduction' => $this->attendance_deduction,
            'leave_deduction' => $this->leave_deduction,
            'overtime_amount' => $this->overtime_amount,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'metadata' => $this->metadata,
            'employee' => EmployeeResource::make($this->whenLoaded('employee')),
            'payroll_run' => PayrollRunResource::make($this->whenLoaded('payrollRun')),
            'components' => $this->whenLoaded('components'),
        ];
    }
}
