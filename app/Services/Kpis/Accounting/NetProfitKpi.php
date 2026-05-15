<?php

namespace App\Services\Kpis\Accounting;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Models\Company;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class NetProfitKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function __construct(private readonly RevenueKpi $revenueKpi, private readonly ExpensesKpi $expensesKpi) {}

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('accounting.net_profit', 'accounting', 'صافي الربح', 'Net profit', 'financial_reports.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $revenue = (float) $this->revenueKpi->resolve($company, $dateRange)->value;
        $expenses = (float) $this->expensesKpi->resolve($company, $dateRange)->value;

        return $this->result($dateRange, round($revenue - $expenses, 2), unit: 'currency', metadata: [
            'revenue' => $revenue,
            'expenses' => $expenses,
        ]);
    }
}
