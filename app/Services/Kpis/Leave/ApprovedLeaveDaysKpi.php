<?php

namespace App\Services\Kpis\Leave;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Enums\LeaveRequestStatus;
use App\Models\Company;
use App\Models\LeaveRequest;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class ApprovedLeaveDaysKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('leave.approved_days', 'leave', 'أيام الإجازة المعتمدة', 'Approved leave days', 'leave_requests.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $value = (float) LeaveRequest::query()
            ->forCompany($company)
            ->where('status', LeaveRequestStatus::Approved->value)
            ->whereDate('start_date', '<=', $dateRange->end->toDateString())
            ->whereDate('end_date', '>=', $dateRange->start->toDateString())
            ->sum('total_days');

        return $this->result($dateRange, $value, unit: 'days');
    }
}
