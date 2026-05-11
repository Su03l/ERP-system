<?php

namespace App\Http\Requests;

use App\Enums\SalaryPackageStatus;
use App\Models\EmployeeSalaryPackage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEmployeeSalaryPackageRequest extends FormRequest
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
            'employee_id' => ['required', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'housing_allowance' => ['nullable', 'numeric', 'min:0'],
            'transportation_allowance' => ['nullable', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'status' => ['sometimes', Rule::enum(SalaryPackageStatus::class)],
            'metadata' => ['nullable', 'array'],
            'items' => ['sometimes', 'array'],
            'items.*.salary_component_id' => ['required_with:items', 'integer', Rule::exists('salary_components', 'id')->where('company_id', $companyId)],
            'items.*.amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('employee_id') || $validator->errors()->has('effective_from') || $validator->errors()->has('effective_to')) {
                    return;
                }

                if ($this->activePackageConflictExists()) {
                    $validator->errors()->add('effective_from', __('validation.custom.salary_packages.active_conflict'));
                }
            },
        ];
    }

    protected function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }

    private function activePackageConflictExists(): bool
    {
        $status = (string) $this->input('status', SalaryPackageStatus::Active->value);

        if ($status !== SalaryPackageStatus::Active->value) {
            return false;
        }

        $startsOn = (string) $this->input('effective_from');
        $endsOn = $this->input('effective_to');

        return EmployeeSalaryPackage::query()
            ->where('company_id', $this->companyId())
            ->where('employee_id', $this->integer('employee_id'))
            ->where('status', SalaryPackageStatus::Active->value)
            ->whereDate('effective_from', '<=', $endsOn ?: '9999-12-31')
            ->where(function ($query) use ($startsOn): void {
                $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $startsOn);
            })
            ->exists();
    }
}
