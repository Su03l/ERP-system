<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GeneratePayrollRunRequest extends FormRequest
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
        $companyId = $this->companyId();

        return [
            'company_id' => ['prohibited'],
            'payroll_period_id' => ['required', 'integer', Rule::exists('payroll_periods', 'id')->where('company_id', $companyId)],
            'run_number' => ['nullable', 'string', 'max:255', Rule::unique('payroll_runs', 'run_number')->where('company_id', $companyId)],
            'allow_duplicate' => ['sometimes', 'boolean'],
            'employee_ids' => ['sometimes', 'array'],
            'employee_ids.*' => ['integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'metadata' => ['nullable', 'array'],
        ];
    }

    private function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }
}
