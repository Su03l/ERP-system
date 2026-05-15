<?php

namespace App\Services\Kpis\Payroll;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Models\Company;
use App\Models\PayrollRunItem;
use App\Services\Kpis\Concerns\ResolvesKpiResults;
use Illuminate\Database\Eloquent\Builder;

class AverageSalaryKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('payroll.average_salary', 'payroll', 'متوسط الراتب', 'Average salary', 'payroll_runs.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $query = $this->itemQuery($company, $dateRange);
        $employees = (clone $query)->distinct('employee_id')->count('employee_id');
        $total = (float) (clone $query)->sum('net_salary');
        $value = $employees === 0 ? 0.0 : round($total / $employees, 2);

        return $this->result($dateRange, $value, unit: 'currency', metadata: ['employee_count' => $employees]);
    }

    /** @return Builder<PayrollRunItem> */
    private function itemQuery(Company $company, KpiDateRange $dateRange): Builder
    {
        return PayrollRunItem::query()
            ->forCompany($company)
            ->whereHas('payrollRun', fn (Builder $query): Builder => $query
                ->whereDate('generated_at', '>=', $dateRange->start->toDateString())
                ->whereDate('generated_at', '<=', $dateRange->end->toDateString()));
    }
}
