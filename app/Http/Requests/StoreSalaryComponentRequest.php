<?php

namespace App\Http\Requests;

use App\Enums\SalaryCalculationType;
use App\Enums\SalaryComponentStatus;
use App\Enums\SalaryComponentType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalaryComponentRequest extends FormRequest
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
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', Rule::unique('salary_components', 'code')->where('company_id', $companyId)],
            'type' => ['required', Rule::enum(SalaryComponentType::class)],
            'calculation_type' => ['required', Rule::enum(SalaryCalculationType::class)],
            'default_amount' => ['nullable', 'numeric', 'min:0'],
            'default_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_taxable' => ['sometimes', 'boolean'],
            'is_recurring' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::enum(SalaryComponentStatus::class)],
            'metadata' => ['nullable', 'array'],
        ];
    }

    private function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }
}
