<?php

namespace App\Services\Kpis\Accounting;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Enums\InvoiceStatus;
use App\Models\Company;
use App\Models\SalesInvoice;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class ReceivablesKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('accounting.receivables', 'accounting', 'الذمم المدينة', 'Receivables', 'financial_reports.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $value = (float) SalesInvoice::query()
            ->forCompany($company)
            ->whereNotIn('status', [InvoiceStatus::Cancelled->value, InvoiceStatus::Voided->value])
            ->whereDate('invoice_date', '<=', $dateRange->end->toDateString())
            ->sum('balance_due');

        return $this->result($dateRange, round($value, 2), unit: 'currency');
    }
}
