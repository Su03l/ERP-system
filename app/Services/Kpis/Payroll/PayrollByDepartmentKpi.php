<?php

namespace App\Services\Kpis\Payroll;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Models\Company;
use App\Models\PayrollRunItem;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class PayrollByDepartmentKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('payroll.by_department', 'payroll', 'الرواتب حسب القسم', 'Payroll by department', 'payroll_runs.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $values = PayrollRunItem::query()
            ->forCompany($company)
            ->whereHas('payrollRun', fn ($query) => $query->whereBetween('generated_at', [$dateRange->start, $dateRange->end]))
            ->join('employees', 'employees.id', '=', 'payroll_run_items.employee_id')
            ->leftJoin('departments', 'departments.id', '=', 'employees.department_id')
            ->select('employees.department_id')
            ->selectRaw('COALESCE(departments.name_ar, departments.name_en, ?) as department_label', [__('hr.metrics.unassigned_department')])
            ->selectRaw('SUM(payroll_run_items.net_salary) as payroll_total')
            ->selectRaw('COUNT(DISTINCT payroll_run_items.employee_id) as employees_count')
            ->groupBy('employees.department_id', 'departments.name_ar', 'departments.name_en')
            ->orderBy('department_label')
            ->get()
            ->map(fn (object $row): array => [
                'department_id' => $row->department_id === null ? null : (int) $row->department_id,
                'label' => (string) $row->department_label,
                'total_payroll_cost' => round((float) $row->payroll_total, 2),
                'employee_count' => (int) $row->employees_count,
            ])
            ->values()
            ->all();

        return $this->result($dateRange, count($values), unit: 'departments', metadata: ['values' => $values]);
    }
}
