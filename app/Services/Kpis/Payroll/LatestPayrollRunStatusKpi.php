<?php

namespace App\Services\Kpis\Payroll;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Models\Company;
use App\Models\PayrollRun;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class LatestPayrollRunStatusKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('payroll.latest_run_status', 'payroll', 'حالة آخر مسير رواتب', 'Latest payroll run status', 'payroll_runs.view', supportsDateRange: false, defaultDateRange: null);
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $run = PayrollRun::query()->forCompany($company)->latest('generated_at')->latest('id')->first();

        return $this->result($dateRange, $run?->status?->value, formattedValue: $run?->status?->label(), metadata: [
            'payroll_run_id' => $run?->id,
            'run_number' => $run?->run_number,
            'generated_at' => $run?->generated_at?->toDateTimeString(),
        ]);
    }
}
