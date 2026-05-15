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

class TotalPayrollCostKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('payroll.total_cost', 'payroll', 'إجمالي تكلفة الرواتب', 'Total payroll cost', 'payroll_runs.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $value = round((float) $this->itemQuery($company, $dateRange)->sum('net_salary'), 2);

        return $this->result($dateRange, $value, unit: 'currency');
    }

    /** @return Builder<PayrollRunItem> */
    protected function itemQuery(Company $company, KpiDateRange $dateRange): Builder
    {
        return PayrollRunItem::query()
            ->forCompany($company)
            ->whereHas('payrollRun', fn (Builder $query): Builder => $query
                ->whereDate('generated_at', '>=', $dateRange->start->toDateString())
                ->whereDate('generated_at', '<=', $dateRange->end->toDateString()));
    }
}
