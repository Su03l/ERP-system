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

class OverdueInvoicesKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('accounting.overdue_invoices', 'accounting', 'الفواتير المتأخرة', 'Overdue invoices', 'financial_reports.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $value = SalesInvoice::query()
            ->forCompany($company)
            ->where(function ($query): void {
                $query->where('status', InvoiceStatus::Overdue->value)
                    ->orWhere(fn ($query) => $query->whereNotIn('status', [InvoiceStatus::Paid->value, InvoiceStatus::Cancelled->value, InvoiceStatus::Voided->value])
                        ->whereNotNull('due_date')
                        ->whereDate('due_date', '<', now()->toDateString()));
            })
            ->whereDate('invoice_date', '<=', $dateRange->end->toDateString())
            ->count();

        return $this->result($dateRange, $value, unit: 'invoices');
    }
}
