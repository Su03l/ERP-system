<?php

namespace App\Services\Kpis\Accounting;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Enums\AccountType;
use App\Enums\JournalEntryStatus;
use App\Models\Company;
use App\Models\JournalEntryLine;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class RevenueKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('accounting.revenue', 'accounting', 'الإيرادات', 'Revenue', 'financial_reports.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $credit = (float) $this->postedLines($company, $dateRange, AccountType::Revenue)->sum('credit');
        $debit = (float) $this->postedLines($company, $dateRange, AccountType::Revenue)->sum('debit');

        return $this->result($dateRange, round($credit - $debit, 2), unit: 'currency');
    }

    private function postedLines(Company $company, KpiDateRange $dateRange, AccountType $type)
    {
        return JournalEntryLine::query()
            ->forCompany($company)
            ->whereHas('account', fn ($query) => $query->where('type', $type->value))
            ->whereHas('journalEntry', fn ($query) => $query
                ->where('status', JournalEntryStatus::Posted->value)
                ->whereDate('entry_date', '>=', $dateRange->start->toDateString())
                ->whereDate('entry_date', '<=', $dateRange->end->toDateString()));
    }
}
