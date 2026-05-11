<?php

namespace App\Http\Requests;

use App\Enums\SalaryPackageStatus;
use App\Models\EmployeeSalaryPackage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateEmployeeSalaryPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $salaryPackage = $this->routeSalaryPackage();

        return $salaryPackage !== null
            && $this->user()?->company_id !== null
            && $this->user()->company_id === $salaryPackage->company_id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->companyId();

        return [
            'company_id' => ['prohibited'],
            'employee_id' => ['sometimes', 'required', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'basic_salary' => ['sometimes', 'required', 'numeric', 'min:0'],
            'housing_allowance' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'transportation_allowance' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'effective_from' => ['sometimes', 'required', 'date'],
            'effective_to' => ['sometimes', 'nullable', 'date', 'after_or_equal:effective_from'],
            'status' => ['sometimes', Rule::enum(SalaryPackageStatus::class)],
            'metadata' => ['sometimes', 'nullable', 'array'],
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
                $salaryPackage = $this->routeSalaryPackage();

                if ($salaryPackage === null || $validator->errors()->has('employee_id') || $validator->errors()->has('effective_from') || $validator->errors()->has('effective_to')) {
                    return;
                }

                if ($this->activePackageConflictExists($salaryPackage)) {
                    $validator->errors()->add('effective_from', __('validation.custom.salary_packages.active_conflict'));
                }
            },
        ];
    }

    private function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }

    private function routeSalaryPackage(): ?EmployeeSalaryPackage
    {
        $salaryPackage = $this->route('employee_salary_package') ?? $this->route('employeeSalaryPackage') ?? $this->route('salary_package') ?? $this->route('salaryPackage');

        if ($salaryPackage instanceof EmployeeSalaryPackage) {
            return $salaryPackage;
        }

        return $salaryPackage === null ? null : EmployeeSalaryPackage::query()->find($salaryPackage);
    }

    private function activePackageConflictExists(EmployeeSalaryPackage $ignore): bool
    {
        $status = (string) $this->input('status', $ignore->status->value);

        if ($status !== SalaryPackageStatus::Active->value) {
            return false;
        }

        $employeeId = (int) ($this->input('employee_id') ?? $ignore->employee_id);
        $startsOn = (string) ($this->input('effective_from') ?? $ignore->effective_from?->toDateString());
        $endsOn = $this->input('effective_to') ?? $ignore->effective_to?->toDateString();

        return EmployeeSalaryPackage::query()
            ->where('company_id', $this->companyId())
            ->where('employee_id', $employeeId)
            ->where('status', SalaryPackageStatus::Active->value)
            ->whereKeyNot($ignore->id)
            ->whereDate('effective_from', '<=', $endsOn ?: '9999-12-31')
            ->where(function ($query) use ($startsOn): void {
                $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $startsOn);
            })
            ->exists();
    }
}
