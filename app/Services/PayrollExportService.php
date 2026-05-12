<?php

namespace App\Services;

use App\Models\PayrollRun;
use App\Models\PayrollRunItem;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PayrollExportService
{
    public function __construct(
        private readonly PayslipDataService $payslipDataService,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array{entity_type: string, module_key: string, columns: array<int, array{key: string, label: string}>, rows: array<int, array<string, mixed>>}
     */
    public function runSummary(array $filters = [], ?User $actor = null): array
    {
        [$actor, $companyId] = $this->authorize($actor, 'payroll_runs.export');

        $rows = PayrollRun::query()
            ->forCompany($companyId)
            ->with('payrollPeriod')
            ->when($filters['payroll_period_id'] ?? null, fn (Builder $query, int|string $periodId): Builder => $query->where('payroll_period_id', $periodId))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->orderByDesc('id')
            ->get()
            ->map(fn (PayrollRun $run): array => [
                'run_number' => $run->run_number,
                'period' => $run->payrollPeriod?->name_ar ?? $run->payrollPeriod?->name_en,
                'status_label' => $run->status?->label(),
                'total_employees' => $run->total_employees,
                'gross_amount' => $run->gross_amount,
                'total_allowances' => $run->total_allowances,
                'total_deductions' => $run->total_deductions,
                'net_amount' => $run->net_amount,
                'generated_at' => $run->generated_at?->toDateTimeString(),
                'approved_at' => $run->approved_at?->toDateTimeString(),
            ])
            ->values()
            ->all();

        return [
            'entity_type' => 'payroll_runs',
            'module_key' => 'payroll',
            'columns' => $this->runSummaryColumns(),
            'rows' => $rows,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{entity_type: string, module_key: string, columns: array<int, array{key: string, label: string}>, rows: array<int, array<string, mixed>>}
     */
    public function runItems(array $filters = [], ?User $actor = null): array
    {
        [$actor, $companyId] = $this->authorize($actor, 'payroll_runs.export');

        $rows = PayrollRunItem::query()
            ->forCompany($companyId)
            ->with(['employee.department', 'employee.jobTitle', 'payrollRun.payrollPeriod'])
            ->when($filters['payroll_run_id'] ?? null, fn (Builder $query, int|string $runId): Builder => $query->where('payroll_run_id', $runId))
            ->when($filters['employee_id'] ?? null, fn (Builder $query, int|string $employeeId): Builder => $query->where('employee_id', $employeeId))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->orderBy('employee_id')
            ->orderBy('id')
            ->get()
            ->map(fn (PayrollRunItem $item): array => $this->mapRunItem($item))
            ->values()
            ->all();

        return [
            'entity_type' => 'payroll_run_items',
            'module_key' => 'payroll',
            'columns' => $this->runItemColumns(),
            'rows' => $rows,
        ];
    }

    /**
     * @return array{entity_type: string, module_key: string, rows: array<int, array<string, mixed>>}
     */
    public function payslips(PayrollRun $payrollRun, ?User $actor = null): array
    {
        [$actor, $companyId] = $this->authorize($actor, 'payslips.view');

        if ($payrollRun->company_id !== $companyId) {
            throw new AuthorizationException('Payroll run does not belong to the current company.');
        }

        $rows = $payrollRun->items()
            ->with(['employee.department', 'employee.jobTitle', 'payrollRun.payrollPeriod', 'company', 'components'])
            ->orderBy('employee_id')
            ->get()
            ->map(fn (PayrollRunItem $item): array => $this->payslipDataService->make($item))
            ->values()
            ->all();

        return [
            'entity_type' => 'payslips',
            'module_key' => 'payroll',
            'rows' => $rows,
        ];
    }

    /**
     * @return array{0: User, 1: int}
     */
    private function authorize(?User $actor, string $permission): array
    {
        $actor ??= Auth::user();
        $companyId = $this->tenantContext->companyId();

        if (! $actor instanceof User || $companyId === null || ! $actor->hasPermission($permission, $companyId)) {
            throw new AuthorizationException('You are not authorized to export payroll data.');
        }

        return [$actor, $companyId];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapRunItem(PayrollRunItem $item): array
    {
        $employee = $item->employee;

        return [
            'run_number' => $item->payrollRun?->run_number,
            'period' => $item->payrollRun?->payrollPeriod?->name_ar ?? $item->payrollRun?->payrollPeriod?->name_en,
            'employee_number' => $employee?->employee_number,
            'employee_name_ar' => $employee ? trim("{$employee->first_name_ar} {$employee->last_name_ar}") : null,
            'department' => $employee?->department?->name_ar ?? $employee?->department?->name_en,
            'job_title' => $employee?->jobTitle?->name_ar ?? $employee?->jobTitle?->name_en,
            'basic_salary' => $item->basic_salary,
            'gross_salary' => $item->gross_salary,
            'total_allowances' => $item->total_allowances,
            'total_deductions' => $item->total_deductions,
            'net_salary' => $item->net_salary,
            'overtime_amount' => $item->overtime_amount,
            'status_label' => $item->status?->label(),
        ];
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    private function runSummaryColumns(): array
    {
        return [
            ['key' => 'run_number', 'label' => __('payroll.export.columns.run_number')],
            ['key' => 'period', 'label' => __('payroll.export.columns.period')],
            ['key' => 'status_label', 'label' => __('payroll.export.columns.status')],
            ['key' => 'total_employees', 'label' => __('payroll.export.columns.total_employees')],
            ['key' => 'gross_amount', 'label' => __('payroll.export.columns.gross_amount')],
            ['key' => 'total_allowances', 'label' => __('payroll.export.columns.total_allowances')],
            ['key' => 'total_deductions', 'label' => __('payroll.export.columns.total_deductions')],
            ['key' => 'net_amount', 'label' => __('payroll.export.columns.net_amount')],
            ['key' => 'generated_at', 'label' => __('payroll.export.columns.generated_at')],
            ['key' => 'approved_at', 'label' => __('payroll.export.columns.approved_at')],
        ];
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    private function runItemColumns(): array
    {
        return [
            ['key' => 'run_number', 'label' => __('payroll.export.columns.run_number')],
            ['key' => 'period', 'label' => __('payroll.export.columns.period')],
            ['key' => 'employee_number', 'label' => __('payroll.export.columns.employee_number')],
            ['key' => 'employee_name_ar', 'label' => __('payroll.export.columns.employee_name_ar')],
            ['key' => 'department', 'label' => __('payroll.export.columns.department')],
            ['key' => 'job_title', 'label' => __('payroll.export.columns.job_title')],
            ['key' => 'basic_salary', 'label' => __('payroll.export.columns.basic_salary')],
            ['key' => 'gross_salary', 'label' => __('payroll.export.columns.gross_salary')],
            ['key' => 'total_allowances', 'label' => __('payroll.export.columns.total_allowances')],
            ['key' => 'total_deductions', 'label' => __('payroll.export.columns.total_deductions')],
            ['key' => 'net_salary', 'label' => __('payroll.export.columns.net_salary')],
            ['key' => 'overtime_amount', 'label' => __('payroll.export.columns.overtime_amount')],
            ['key' => 'status_label', 'label' => __('payroll.export.columns.status')],
        ];
    }
}
