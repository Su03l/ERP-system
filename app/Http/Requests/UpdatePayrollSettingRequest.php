<?php

namespace App\Http\Requests;

use App\Enums\PayrollCycleType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePayrollSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->company_id !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['prohibited'],
            'payroll_cycle_type' => ['sometimes', Rule::enum(PayrollCycleType::class)],
            'default_pay_day' => ['sometimes', 'nullable', 'integer', 'between:1,31'],
            'overtime_calculation_enabled' => ['sometimes', 'boolean'],
            'absence_deduction_enabled' => ['sometimes', 'boolean'],
            'late_deduction_enabled' => ['sometimes', 'boolean'],
            'default_currency' => ['sometimes', 'required', 'string', 'size:3'],
            'payslip_language' => ['sometimes', 'required', Rule::in(['ar', 'en'])],
            'payroll_approval_required' => ['sometimes', 'boolean'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
