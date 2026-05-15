<?php

namespace App\Services\Kpis\Projects;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Models\Company;
use App\Models\ProjectTimeLog;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class BillableHoursKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('projects.billable_hours', 'projects', 'الساعات القابلة للفوترة', 'Billable hours', 'project_time_logs.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $minutes = (int) ProjectTimeLog::query()->forCompany($company)->where('is_billable', true)->whereDate('log_date', '>=', $dateRange->start->toDateString())->whereDate('log_date', '<=', $dateRange->end->toDateString())->sum('total_minutes');
        $hours = round($minutes / 60, 2);

        return $this->result($dateRange, $hours, formattedValue: "{$hours}h", unit: 'hours');
    }
}
