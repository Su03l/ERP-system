<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Department;
use App\Models\JobTitle;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EmployeeImportDefinition
{
    public const EntityKey = 'employees';

    public const ModuleKey = 'hr';

    /**
     * @return array{
     *     entity_type: string,
     *     module_key: string,
     *     columns: array<int, array{key: string, label: string, required: bool, aliases: array<int, string>}>
     * }
     */
    public function definition(): array
    {
        return [
            'entity_type' => self::EntityKey,
            'module_key' => self::ModuleKey,
            'columns' => $this->columns(),
        ];
    }

    /**
     * @return array<int, array{key: string, label: string, required: bool, aliases: array<int, string>}>
     */
    public function columns(): array
    {
        return [
            $this->column('employee_number', __('hr.import.columns.employee_number'), true, ['رقم الموظف', 'Employee Number']),
            $this->column('first_name_ar', __('hr.import.columns.first_name_ar'), true, ['الاسم الأول', 'First Name AR']),
            $this->column('last_name_ar', __('hr.import.columns.last_name_ar'), true, ['اسم العائلة', 'Last Name AR']),
            $this->column('email', __('hr.import.columns.email'), false, ['البريد الإلكتروني', 'Email']),
            $this->column('phone', __('hr.import.columns.phone'), false, ['الهاتف', 'Phone']),
            $this->column('department', __('hr.import.columns.department'), false, ['الإدارة', 'Department']),
            $this->column('job_title', __('hr.import.columns.job_title'), false, ['المسمى الوظيفي', 'Job Title']),
            $this->column('hire_date', __('hr.import.columns.hire_date'), false, ['تاريخ التعيين', 'Hire Date']),
            $this->column('basic_salary', __('hr.import.columns.basic_salary'), false, ['الراتب الأساسي', 'Basic Salary']),
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(Company|int $company): array
    {
        $companyId = $company instanceof Company ? $company->id : $company;

        return [
            'employee_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('employees', 'employee_number')->where('company_id', $companyId),
            ],
            'first_name_ar' => ['required', 'string', 'max:255'],
            'last_name_ar' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:255', $this->tenantLookupRule(Department::class, $companyId)],
            'job_title' => ['nullable', 'string', 'max:255', $this->tenantLookupRule(JobTitle::class, $companyId)],
            'hire_date' => ['nullable', 'date'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{valid: bool, data: array<string, mixed>, errors: array<string, array<int, string>>}
     */
    public function validateRow(array $row, Company|int $company): array
    {
        $companyId = $company instanceof Company ? $company->id : $company;
        $validator = Validator::make($row, $this->rules($companyId));
        $valid = ! $validator->fails();

        return [
            'valid' => $valid,
            'data' => $valid ? $this->mapValidatedRow($validator->validated(), $companyId) : $row,
            'errors' => $validator->errors()->toArray(),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{entity_type: string, module_key: string, total_rows: int, preview_rows: array<int, array{row_number: int, valid: bool, data: array<string, mixed>, errors: array<string, array<int, string>>}>}
     */
    public function preview(array $rows, Company|int $company, int $limit = 10): array
    {
        return [
            'entity_type' => self::EntityKey,
            'module_key' => self::ModuleKey,
            'total_rows' => count($rows),
            'preview_rows' => collect($rows)
                ->take(max(0, $limit))
                ->map(function (array $row, int $index) use ($company): array {
                    $validatedRow = $this->validateRow($row, $company);

                    return [
                        'row_number' => $index + 1,
                        ...$validatedRow,
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function mapValidatedRow(array $row, int $companyId): array
    {
        return [
            'employee_number' => $row['employee_number'],
            'first_name_ar' => $row['first_name_ar'],
            'last_name_ar' => $row['last_name_ar'],
            'email' => $row['email'] ?? null,
            'phone' => $row['phone'] ?? null,
            'department_id' => $this->resolveDepartmentId($row['department'] ?? null, $companyId),
            'job_title_id' => $this->resolveJobTitleId($row['job_title'] ?? null, $companyId),
            'hire_date' => $row['hire_date'] ?? null,
            'basic_salary' => $row['basic_salary'] ?? null,
        ];
    }

    /**
     * @param  class-string<Department|JobTitle>  $modelClass
     */
    private function tenantLookupRule(string $modelClass, int $companyId): callable
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($companyId, $modelClass): void {
            if ($value === null || $value === '') {
                return;
            }

            if (! $this->lookupExists($modelClass, (string) $value, $companyId)) {
                $fail(__('validation.exists', ['attribute' => __('hr.import.columns.'.$attribute)]));
            }
        };
    }

    /**
     * @param  class-string<Department|JobTitle>  $modelClass
     */
    private function lookupExists(string $modelClass, string $value, int $companyId): bool
    {
        return $modelClass::query()
            ->where('company_id', $companyId)
            ->where(function ($query) use ($value): void {
                $query->where('code', $value)
                    ->orWhere('name_ar', $value)
                    ->orWhere('name_en', $value);
            })
            ->exists();
    }

    private function resolveDepartmentId(?string $value, int $companyId): ?int
    {
        return $this->resolveLookupId(Department::class, $value, $companyId);
    }

    private function resolveJobTitleId(?string $value, int $companyId): ?int
    {
        return $this->resolveLookupId(JobTitle::class, $value, $companyId);
    }

    /**
     * @param  class-string<Department|JobTitle>  $modelClass
     */
    private function resolveLookupId(string $modelClass, ?string $value, int $companyId): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $modelClass::query()
            ->where('company_id', $companyId)
            ->where(function ($query) use ($value): void {
                $query->where('code', $value)
                    ->orWhere('name_ar', $value)
                    ->orWhere('name_en', $value);
            })
            ->value('id');
    }

    /**
     * @param  array<int, string>  $aliases
     * @return array{key: string, label: string, required: bool, aliases: array<int, string>}
     */
    private function column(string $key, string $label, bool $required, array $aliases): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'required' => $required,
            'aliases' => $aliases,
        ];
    }
}
