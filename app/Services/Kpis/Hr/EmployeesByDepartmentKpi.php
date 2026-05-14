<?php

namespace App\Services\Kpis\Hr;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Models\Company;
use App\Models\Employee;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class EmployeesByDepartmentKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('hr.employees_by_department', 'hr', 'الموظفون حسب القسم', 'Employees by department', 'employees.view', supportsDateRange: false, defaultDateRange: null);
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $values = Employee::query()
            ->forCompany($company)
            ->leftJoin('departments', 'departments.id', '=', 'employees.department_id')
            ->select('employees.department_id')
            ->selectRaw('COALESCE(departments.name_ar, departments.name_en, ?) as department_label', [__('hr.metrics.unassigned_department')])
            ->selectRaw('COUNT(*) as employees_count')
            ->groupBy('employees.department_id', 'departments.name_ar', 'departments.name_en')
            ->orderBy('department_label')
            ->get()
            ->map(fn (object $row): array => [
                'department_id' => $row->department_id === null ? null : (int) $row->department_id,
                'label' => (string) $row->department_label,
                'value' => (int) $row->employees_count,
            ])
            ->values()
            ->all();

        return $this->result($dateRange, count($values), unit: 'departments', metadata: ['values' => $values]);
    }
}
