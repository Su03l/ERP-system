<?php

namespace App\Http\Requests;

use App\Enums\SalaryPackageStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexEmployeeSalaryPackageRequest extends FormRequest
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
            'employee_id' => ['nullable', 'integer', Rule::exists('employees', 'id')->where('company_id', (int) ($this->user()?->company_id ?? 0))],
            'status' => ['nullable', Rule::enum(SalaryPackageStatus::class)],
        ];
    }
}
