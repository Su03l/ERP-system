<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EmployeeExportQuery
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array{entity_type: string, module_key: string, includes_salary: bool, columns: array<int, array{key: string, label: string}>, rows: array<int, array<string, mixed>>}
     */
    public function export(array $filters = [], ?User $actor = null): array
    {
        $actor ??= Auth::user();
        $companyId = $this->tenantContext->companyId();
        $canViewSalary = $actor instanceof User
            && $companyId !== null
            && $actor->hasPermission('employees.view_salary', $companyId);

        if ($companyId === null) {
            return $this->emptyExport($canViewSalary);
        }

        $rows = $this->query($filters, $companyId)
            ->get()
            ->map(fn (Employee $employee): array => $this->mapEmployee($employee, $canViewSalary))
            ->values()
            ->all();

        return [
            'entity_type' => 'employees',
            'module_key' => 'hr',
            'includes_salary' => $canViewSalary,
            'columns' => $this->columns($canViewSalary),
            'rows' => $rows,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Employee>
     */
    private function query(array $filters, int $companyId): Builder
    {
        return Employee::query()
            ->forCompany($companyId)
            ->with(['department', 'jobTitle', 'manager'])
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('employee_number', 'like', "%{$search}%")
                        ->orWhere('first_name_ar', 'like', "%{$search}%")
                        ->orWhere('last_name_ar', 'like', "%{$search}%")
                        ->orWhere('first_name_en', 'like', "%{$search}%")
                        ->orWhere('last_name_en', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($filters['department_id'] ?? null, fn (Builder $query, int|string $departmentId): Builder => $query->where('department_id', $departmentId))
            ->when($filters['job_title_id'] ?? null, fn (Builder $query, int|string $jobTitleId): Builder => $query->where('job_title_id', $jobTitleId))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('employment_status', $status))
            ->when($filters['hired_from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('hire_date', '>=', $date))
            ->when($filters['hired_until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('hire_date', '<=', $date))
            ->orderBy('employee_number')
            ->orderBy('id');
    }

    /**
     * @return array<string, mixed>
     */
    private function mapEmployee(Employee $employee, bool $canViewSalary): array
    {
        $row = [
            'employee_number' => $employee->employee_number,
            'name_ar' => trim("{$employee->first_name_ar} {$employee->last_name_ar}"),
            'name_en' => $this->localizedName($employee->first_name_en, $employee->last_name_en),
            'email' => $employee->email,
            'phone' => $employee->phone,
            'department' => $employee->department?->name_ar ?? $employee->department?->name_en,
            'job_title' => $employee->jobTitle?->name_ar ?? $employee->jobTitle?->name_en,
            'manager' => $employee->manager ? trim("{$employee->manager->first_name_ar} {$employee->manager->last_name_ar}") : null,
            'hire_date' => $employee->hire_date?->toDateString(),
            'employment_status' => $employee->employment_status?->value,
            'employment_status_label' => $employee->employment_status?->label(),
            'work_type' => $employee->work_type?->value,
            'work_type_label' => $employee->work_type?->label(),
        ];

        if ($canViewSalary) {
            $row['basic_salary'] = $employee->basic_salary;
        }

        return $row;
    }

    private function localizedName(?string $firstName, ?string $lastName): ?string
    {
        $name = trim("{$firstName} {$lastName}");

        return $name === '' ? null : $name;
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    private function columns(bool $includeSalary): array
    {
        $columns = [
            ['key' => 'employee_number', 'label' => __('hr.export.columns.employee_number')],
            ['key' => 'name_ar', 'label' => __('hr.export.columns.name_ar')],
            ['key' => 'name_en', 'label' => __('hr.export.columns.name_en')],
            ['key' => 'email', 'label' => __('hr.export.columns.email')],
            ['key' => 'phone', 'label' => __('hr.export.columns.phone')],
            ['key' => 'department', 'label' => __('hr.export.columns.department')],
            ['key' => 'job_title', 'label' => __('hr.export.columns.job_title')],
            ['key' => 'manager', 'label' => __('hr.export.columns.manager')],
            ['key' => 'hire_date', 'label' => __('hr.export.columns.hire_date')],
            ['key' => 'employment_status_label', 'label' => __('hr.export.columns.employment_status')],
            ['key' => 'work_type_label', 'label' => __('hr.export.columns.work_type')],
        ];

        if ($includeSalary) {
            $columns[] = ['key' => 'basic_salary', 'label' => __('hr.export.columns.basic_salary')];
        }

        return $columns;
    }

    /**
     * @return array{entity_type: string, module_key: string, includes_salary: bool, columns: array<int, array{key: string, label: string}>, rows: array<int, array<string, mixed>>}
     */
    private function emptyExport(bool $canViewSalary): array
    {
        return [
            'entity_type' => 'employees',
            'module_key' => 'hr',
            'includes_salary' => $canViewSalary,
            'columns' => $this->columns($canViewSalary),
            'rows' => [],
        ];
    }
}
