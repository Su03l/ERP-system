<?php

namespace App\Services;

use App\Models\Company;
use App\Models\PayrollRun;
use App\Models\PayrollRunItem;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PayrollMetrics
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function forCurrentCompany(array $filters = [], ?User $actor = null): array
    {
        $actor ??= Auth::user();
        $companyId = $this->tenantContext->companyId();

        if (! $actor instanceof User || $companyId === null || ! $actor->hasPermission('payroll_runs.view', $companyId)) {
            throw new AuthorizationException('You are not authorized to view payroll metrics.');
        }

        return $this->forCompany($companyId, $filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function forCompany(Company|int $company, array $filters = []): array
    {
        $companyId = $company instanceof Company ? $company->id : $company;
        $itemQuery = $this->itemQuery($companyId, $filters);
        $runQuery = $this->runQuery($companyId, $filters);
        $employeeCount = (clone $itemQuery)->distinct('employee_id')->count('employee_id');
        $totalPayrollCost = (float) (clone $itemQuery)->sum('net_salary');

        return [
            'total_payroll_cost' => $this->metric('total_payroll_cost', __('payroll.metrics.total_payroll_cost'), round($totalPayrollCost, 2)),
            'average_salary' => $this->metric('average_salary', __('payroll.metrics.average_salary'), $employeeCount === 0 ? 0.0 : round($totalPayrollCost / $employeeCount, 2)),
            'total_allowances' => $this->metric('total_allowances', __('payroll.metrics.total_allowances'), round((float) (clone $itemQuery)->sum('total_allowances'), 2)),
            'total_deductions' => $this->metric('total_deductions', __('payroll.metrics.total_deductions'), round((float) (clone $itemQuery)->sum('total_deductions'), 2)),
            'overtime_cost' => $this->metric('overtime_cost', __('payroll.metrics.overtime_cost'), round((float) (clone $itemQuery)->sum('overtime_amount'), 2)),
            'payroll_by_department' => [
                'key' => 'payroll_by_department',
                'label' => __('payroll.metrics.payroll_by_department'),
                'values' => $this->payrollByDepartment($companyId, $filters),
            ],
            'payroll_trend_by_period' => [
                'key' => 'payroll_trend_by_period',
                'label' => __('payroll.metrics.payroll_trend_by_period'),
                'values' => $this->payrollTrendByPeriod($runQuery),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<PayrollRunItem>
     */
    private function itemQuery(int $companyId, array $filters): Builder
    {
        return PayrollRunItem::query()
            ->forCompany($companyId)
            ->whereHas('payrollRun', fn (Builder $query): Builder => $this->applyRunFilters($query, $filters));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<PayrollRun>
     */
    private function runQuery(int $companyId, array $filters): Builder
    {
        return $this->applyRunFilters(PayrollRun::query()->forCompany($companyId), $filters);
    }

    /**
     * @param  Builder<PayrollRun>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<PayrollRun>
     */
    private function applyRunFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['payroll_period_id'] ?? null, fn (Builder $query, int|string $periodId): Builder => $query->where('payroll_period_id', $periodId))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('generated_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('generated_at', '<=', $date));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array{department_id: int|null, label: string, total_payroll_cost: float, employee_count: int}>
     */
    private function payrollByDepartment(int $companyId, array $filters): array
    {
        return $this->itemQuery($companyId, $filters)
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
    }

    /**
     * @param  Builder<PayrollRun>  $query
     * @return array<int, array{payroll_period_id: int, label: string|null, net_amount: float, gross_amount: float}>
     */
    private function payrollTrendByPeriod(Builder $query): array
    {
        return $query
            ->with('payrollPeriod')
            ->orderBy('payroll_period_id')
            ->get()
            ->map(fn (PayrollRun $run): array => [
                'payroll_period_id' => $run->payroll_period_id,
                'label' => $run->payrollPeriod?->name_ar ?? $run->payrollPeriod?->name_en,
                'net_amount' => round((float) $run->net_amount, 2),
                'gross_amount' => round((float) $run->gross_amount, 2),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{key: string, label: string, value: int|float}
     */
    private function metric(string $key, string $label, int|float $value): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'value' => $value,
        ];
    }
}
