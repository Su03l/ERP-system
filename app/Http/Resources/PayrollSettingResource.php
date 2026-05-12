<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'payroll_cycle_type' => $this->payroll_cycle_type?->value,
            'payroll_cycle_type_label' => $this->payroll_cycle_type?->label(),
            'default_pay_day' => $this->default_pay_day,
            'overtime_calculation_enabled' => $this->overtime_calculation_enabled,
            'absence_deduction_enabled' => $this->absence_deduction_enabled,
            'late_deduction_enabled' => $this->late_deduction_enabled,
            'default_currency' => $this->default_currency,
            'payslip_language' => $this->payslip_language,
            'payroll_approval_required' => $this->payroll_approval_required,
            'metadata' => $this->metadata,
        ];
    }
}
