<?php

namespace App\Services\Kpis\Accounting;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Enums\InvoiceStatus;
use App\Models\Company;
use App\Models\PurchaseInvoice;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class PayablesKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('accounting.payables', 'accounting', 'الذمم الدائنة', 'Payables', 'financial_reports.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $value = (float) PurchaseInvoice::query()
            ->forCompany($company)
            ->whereNotIn('status', [InvoiceStatus::Cancelled->value, InvoiceStatus::Voided->value])
            ->whereDate('invoice_date', '<=', $dateRange->end->toDateString())
            ->sum('balance_due');

        return $this->result($dateRange, round($value, 2), unit: 'currency');
    }
}
