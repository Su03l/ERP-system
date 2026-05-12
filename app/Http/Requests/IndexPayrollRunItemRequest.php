<?php

namespace App\Http\Requests;

use App\Enums\PayrollRunItemStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexPayrollRunItemRequest extends FormRequest
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
        $companyId = (int) ($this->user()?->company_id ?? 0);

        return [
            'payroll_run_id' => ['nullable', 'integer', Rule::exists('payroll_runs', 'id')->where('company_id', $companyId)],
            'employee_id' => ['nullable', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'status' => ['nullable', Rule::enum(PayrollRunItemStatus::class)],
        ];
    }
}
