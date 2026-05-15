<?php

namespace App\Services\Kpis\Payroll;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Models\Company;
use App\Models\PayrollRunItem;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class TotalDeductionsKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('payroll.total_deductions', 'payroll', 'إجمالي الاستقطاعات', 'Total deductions', 'payroll_runs.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $value = round((float) PayrollRunItem::query()->forCompany($company)->whereHas('payrollRun', fn ($query) => $query->whereBetween('generated_at', [$dateRange->start, $dateRange->end]))->sum('total_deductions'), 2);

        return $this->result($dateRange, $value, unit: 'currency');
    }
}
