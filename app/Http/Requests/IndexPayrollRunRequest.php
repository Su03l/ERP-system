<?php

namespace App\Http\Requests;

use App\Enums\PayrollRunStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexPayrollRunRequest extends FormRequest
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
            'payroll_period_id' => ['nullable', 'integer', Rule::exists('payroll_periods', 'id')->where('company_id', $companyId)],
            'status' => ['nullable', Rule::enum(PayrollRunStatus::class)],
        ];
    }
}
