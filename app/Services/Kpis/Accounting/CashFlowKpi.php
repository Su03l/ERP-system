<?php

namespace App\Services\Kpis\Accounting;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Enums\PaymentDirection;
use App\Enums\PaymentStatus;
use App\Models\Company;
use App\Models\Payment;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class CashFlowKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('accounting.cash_flow', 'accounting', 'صافي التدفق النقدي', 'Cash flow', 'financial_reports.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $base = Payment::query()
            ->forCompany($company)
            ->where('status', PaymentStatus::Completed->value)
            ->whereDate('payment_date', '>=', $dateRange->start->toDateString())
            ->whereDate('payment_date', '<=', $dateRange->end->toDateString());
        $incoming = (float) (clone $base)->where('direction', PaymentDirection::Incoming->value)->sum('amount');
        $outgoing = (float) (clone $base)->where('direction', PaymentDirection::Outgoing->value)->sum('amount');

        return $this->result($dateRange, round($incoming - $outgoing, 2), unit: 'currency', metadata: [
            'incoming' => round($incoming, 2),
            'outgoing' => round($outgoing, 2),
        ]);
    }
}
