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

class PendingLeaveRequestsKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('leave.pending_requests', 'leave', 'طلبات الإجازة المعلقة', 'Pending leave requests', 'leave_requests.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $value = LeaveRequest::query()
            ->forCompany($company)
            ->where('status', LeaveRequestStatus::Pending->value)
            ->whereDate('start_date', '<=', $dateRange->end->toDateString())
            ->whereDate('end_date', '>=', $dateRange->start->toDateString())
            ->count();

        return $this->result($dateRange, $value, unit: 'requests');
    }
}
