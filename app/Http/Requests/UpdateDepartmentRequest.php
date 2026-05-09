<?php

namespace App\Http\Requests;

use App\Enums\DepartmentStatus;
use App\Models\Department;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $department = $this->department();

        return $department instanceof Department
            && ($this->user()?->can('update', $department) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = (int) ($this->user()?->company_id ?? 0);
        $departmentId = $this->department()?->id;

        return [
            'company_id' => ['prohibited'],
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'code' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                Rule::unique('departments', 'code')
                    ->where('company_id', $companyId)
                    ->ignore($departmentId),
            ],
            'parent_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('departments', 'id')->where('company_id', $companyId),
                Rule::notIn(array_filter([$departmentId])),
            ],
            'manager_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('company_id', $companyId),
            ],
            'status' => ['sometimes', 'required', Rule::enum(DepartmentStatus::class)],
            'description' => ['sometimes', 'nullable', 'string'],
        ];
    }

    private function department(): ?Department
    {
        $department = $this->route('department');

        if ($department instanceof Department) {
            return $department;
        }

        if (is_numeric($department)) {
            return Department::query()->find((int) $department);
        }

        return null;
    }
}
