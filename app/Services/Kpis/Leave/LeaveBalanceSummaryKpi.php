<?php

namespace App\Services\Kpis\Leave;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Models\Company;
use App\Models\LeaveBalance;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class LeaveBalanceSummaryKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('leave.balance_summary', 'leave', 'ملخص أرصدة الإجازات', 'Leave balance summary', 'leave_balances.view', supportsDateRange: false, defaultDateRange: null);
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $balances = LeaveBalance::query()
            ->forCompany($company)
            ->selectRaw('SUM(opening_balance) as opening_balance')
            ->selectRaw('SUM(accrued_days) as accrued_days')
            ->selectRaw('SUM(used_days) as used_days')
            ->selectRaw('SUM(remaining_days) as remaining_days')
            ->first();

        $remaining = (float) ($balances?->remaining_days ?? 0);

        return $this->result($dateRange, $remaining, unit: 'days', metadata: [
            'opening_balance' => (float) ($balances?->opening_balance ?? 0),
            'accrued_days' => (float) ($balances?->accrued_days ?? 0),
            'used_days' => (float) ($balances?->used_days ?? 0),
            'remaining_days' => $remaining,
        ]);
    }
}
