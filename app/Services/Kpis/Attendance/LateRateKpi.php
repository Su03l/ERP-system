<?php

namespace App\Services\Kpis\Attendance;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Services\Kpis\Concerns\ResolvesKpiResults;
use Illuminate\Database\Eloquent\Builder;

class LateRateKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('attendance.late_rate', 'attendance', 'معدل التأخير', 'Late rate', 'attendance.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $query = $this->baseQuery($company, $dateRange);
        $total = (clone $query)->count();
        $late = (clone $query)->where('status', AttendanceStatus::Late->value)->count();
        $value = $this->percentage($late, $total);

        return $this->result($dateRange, $value, formattedValue: "{$value}%", unit: 'percent', metadata: ['total_records' => $total]);
    }

    /**
     * @return Builder<AttendanceRecord>
     */
    private function baseQuery(Company $company, KpiDateRange $dateRange): Builder
    {
        return AttendanceRecord::query()
            ->forCompany($company)
            ->whereDate('attendance_date', '>=', $dateRange->start->toDateString())
            ->whereDate('attendance_date', '<=', $dateRange->end->toDateString());
    }
}
