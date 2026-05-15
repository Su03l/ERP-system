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

class ExpensesKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('accounting.expenses', 'accounting', 'المصروفات', 'Expenses', 'financial_reports.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $debit = (float) $this->postedExpenseLines($company, $dateRange)->sum('debit');
        $credit = (float) $this->postedExpenseLines($company, $dateRange)->sum('credit');

        return $this->result($dateRange, round($debit - $credit, 2), unit: 'currency');
    }

    private function postedExpenseLines(Company $company, KpiDateRange $dateRange)
    {
        return JournalEntryLine::query()
            ->forCompany($company)
            ->whereHas('account', fn ($query) => $query->where('type', AccountType::Expense->value))
            ->whereHas('journalEntry', fn ($query) => $query
                ->where('status', JournalEntryStatus::Posted->value)
                ->whereDate('entry_date', '>=', $dateRange->start->toDateString())
                ->whereDate('entry_date', '<=', $dateRange->end->toDateString()));
    }
}
