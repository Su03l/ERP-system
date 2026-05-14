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

class AttendanceRateKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('attendance.attendance_rate', 'attendance', 'معدل الحضور', 'Attendance rate', 'attendance.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $query = $this->baseQuery($company, $dateRange);
        $total = (clone $query)->count();
        $present = (clone $query)->whereIn('status', [AttendanceStatus::Present->value, AttendanceStatus::Late->value])->count();
        $value = $this->percentage($present, $total);

        return $this->result($dateRange, $value, formattedValue: "{$value}%", unit: 'percent', metadata: ['total_records' => $total]);
    }

    /**
     * @return Builder<AttendanceRecord>
     */
    protected function baseQuery(Company $company, KpiDateRange $dateRange): Builder
    {
        return AttendanceRecord::query()
            ->forCompany($company)
            ->whereDate('attendance_date', '>=', $dateRange->start->toDateString())
            ->whereDate('attendance_date', '<=', $dateRange->end->toDateString());
    }
}
