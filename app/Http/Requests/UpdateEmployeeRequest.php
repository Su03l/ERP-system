<?php

namespace App\Http\Requests;

use App\Enums\EmployeeStatus;
use App\Enums\Gender;
use App\Enums\WorkType;
use App\Models\Employee;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->company_id !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->companyId();
        $employeeId = $this->employeeId();

        return [
            'company_id' => ['prohibited'],
            'employee_number' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('employees', 'employee_number')
                    ->where('company_id', $companyId)
                    ->ignore($employeeId),
            ],
            'user_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('company_id', $companyId),
            ],
            'department_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('departments', 'id')->where('company_id', $companyId),
            ],
            'job_title_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('job_titles', 'id')->where('company_id', $companyId),
            ],
            'manager_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('employees', 'id')->where('company_id', $companyId),
                Rule::notIn(array_filter([$employeeId])),
            ],
            'first_name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'first_name_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'last_name_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'national_id' => ['sometimes', 'nullable', 'string', 'max:100'],
            'nationality' => ['sometimes', 'nullable', 'string', 'max:100'],
            'gender' => ['sometimes', 'nullable', Rule::enum(Gender::class)],
            'date_of_birth' => ['sometimes', 'nullable', 'date'],
            'hire_date' => ['sometimes', 'nullable', 'date'],
            'employment_status' => ['sometimes', 'required', Rule::enum(EmployeeStatus::class)],
            'work_type' => ['sometimes', 'nullable', Rule::enum(WorkType::class)],
            'basic_salary' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ];
    }

    private function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }

    private function employeeId(): ?int
    {
        $employee = $this->route('employee');

        if ($employee instanceof Employee) {
            return $employee->id;
        }

        if (is_numeric($employee)) {
            return (int) $employee;
        }

        return null;
    }
}
