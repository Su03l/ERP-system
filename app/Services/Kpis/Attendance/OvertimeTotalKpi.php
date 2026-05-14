<?php

namespace App\Services\Kpis\Attendance;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Services\Kpis\Concerns\ResolvesKpiResults;

class OvertimeTotalKpi implements KpiResolver
{
    use ResolvesKpiResults;

    public function definition(): KpiDefinition
    {
        return $this->definitionFor('attendance.overtime_total', 'attendance', 'إجمالي العمل الإضافي', 'Overtime total', 'attendance.view');
    }

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
    {
        $minutes = (int) AttendanceRecord::query()
            ->forCompany($company)
            ->whereDate('attendance_date', '>=', $dateRange->start->toDateString())
            ->whereDate('attendance_date', '<=', $dateRange->end->toDateString())
            ->sum('overtime_minutes');

        return $this->result($dateRange, $minutes, formattedValue: round($minutes / 60, 2).'h', unit: 'minutes');
    }
}
