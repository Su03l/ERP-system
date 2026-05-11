<?php

namespace App\Http\Requests;

use App\Enums\SalaryCalculationType;
use App\Enums\SalaryComponentStatus;
use App\Enums\SalaryComponentType;
use App\Models\SalaryComponent;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSalaryComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $salaryComponent = $this->routeSalaryComponent();

        return $salaryComponent !== null
            && $this->user()?->company_id !== null
            && $this->user()->company_id === $salaryComponent->company_id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->companyId();
        $salaryComponent = $this->routeSalaryComponent();

        return [
            'company_id' => ['prohibited'],
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'code' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                Rule::unique('salary_components', 'code')
                    ->where('company_id', $companyId)
                    ->ignore($salaryComponent),
            ],
            'type' => ['sometimes', 'required', Rule::enum(SalaryComponentType::class)],
            'calculation_type' => ['sometimes', 'required', Rule::enum(SalaryCalculationType::class)],
            'default_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'default_percentage' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'is_taxable' => ['sometimes', 'boolean'],
            'is_recurring' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::enum(SalaryComponentStatus::class)],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    private function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }

    private function routeSalaryComponent(): ?SalaryComponent
    {
        $salaryComponent = $this->route('salary_component') ?? $this->route('salaryComponent');

        if ($salaryComponent instanceof SalaryComponent) {
            return $salaryComponent;
        }

        return $salaryComponent === null ? null : SalaryComponent::query()->find($salaryComponent);
    }
}
