<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Support\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class AttendanceMetrics
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function forCurrentCompany(array $filters = []): array
    {
        $companyId = $this->tenantContext->companyId();

        if ($companyId === null) {
            return $this->emptyMetrics($filters);
        }

        return $this->forCompany($companyId, $filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function forCompany(Company|int $company, array $filters = []): array
    {
        $companyId = $company instanceof Company ? $company->id : $company;
        [$rangeStart, $rangeEnd] = $this->dateRange($filters);
        $baseQuery = $this->query($companyId, $filters, $rangeStart, $rangeEnd);
        $totalRecords = (clone $baseQuery)->count();
        $presentCount = $this->statusCount($baseQuery, AttendanceStatus::Present);
        $absentCount = $this->statusCount($baseQuery, AttendanceStatus::Absent);
        $lateCount = $this->statusCount($baseQuery, AttendanceStatus::Late);
        $overtimeTotal = (int) (clone $baseQuery)->sum('overtime_minutes');
        $averageWorkMinutes = (float) ((clone $baseQuery)->whereNotNull('total_work_minutes')->avg('total_work_minutes') ?? 0);

        return [
            'date_range' => [
                'start' => $rangeStart->toDateString(),
                'end' => $rangeEnd->toDateString(),
            ],
            'present_count' => $this->metric('present_count', __('hr.attendance.metrics.present_count'), $presentCount),
            'absent_count' => $this->metric('absent_count', __('hr.attendance.metrics.absent_count'), $absentCount),
            'late_count' => $this->metric('late_count', __('hr.attendance.metrics.late_count'), $lateCount),
            'overtime_total' => $this->metric('overtime_total', __('hr.attendance.metrics.overtime_total'), $overtimeTotal),
            'average_work_hours' => $this->metric('average_work_hours', __('hr.attendance.metrics.average_work_hours'), round($averageWorkMinutes / 60, 2)),
            'attendance_rate' => $this->metric('attendance_rate', __('hr.attendance.metrics.attendance_rate'), $this->percentage($presentCount + $lateCount, $totalRecords)),
            'late_rate' => $this->metric('late_rate', __('hr.attendance.metrics.late_rate'), $this->percentage($lateCount, $totalRecords)),
            'absence_rate' => $this->metric('absence_rate', __('hr.attendance.metrics.absence_rate'), $this->percentage($absentCount, $totalRecords)),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<AttendanceRecord>
     */
    private function query(int $companyId, array $filters, CarbonImmutable $rangeStart, CarbonImmutable $rangeEnd): Builder
    {
        return AttendanceRecord::query()
            ->forCompany($companyId)
            ->when($filters['department_id'] ?? null, function (Builder $query, int|string $departmentId): void {
                $query->whereHas('employee', fn (Builder $employeeQuery): Builder => $employeeQuery->where('department_id', $departmentId));
            })
            ->whereDate('attendance_date', '>=', $rangeStart->toDateString())
            ->whereDate('attendance_date', '<=', $rangeEnd->toDateString());
    }

    private function statusCount(Builder $baseQuery, AttendanceStatus $status): int
    {
        return (clone $baseQuery)->where('status', $status->value)->count();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function dateRange(array $filters): array
    {
        $rangeStart = isset($filters['date_from'])
            ? CarbonImmutable::parse((string) $filters['date_from'])->startOfDay()
            : CarbonImmutable::now()->startOfMonth();

        $rangeEnd = isset($filters['date_to'])
            ? CarbonImmutable::parse((string) $filters['date_to'])->endOfDay()
            : CarbonImmutable::now()->endOfMonth();

        return [$rangeStart, $rangeEnd];
    }

    /**
     * @return array{key: string, label: string, value: int|float}
     */
    private function metric(string $key, string $label, int|float $value): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'value' => $value,
        ];
    }

    private function percentage(int $count, int $total): float
    {
        if ($total === 0) {
            return 0.0;
        }

        return round(($count / $total) * 100, 2);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function emptyMetrics(array $filters): array
    {
        [$rangeStart, $rangeEnd] = $this->dateRange($filters);

        return [
            'date_range' => [
                'start' => $rangeStart->toDateString(),
                'end' => $rangeEnd->toDateString(),
            ],
            'present_count' => $this->metric('present_count', __('hr.attendance.metrics.present_count'), 0),
            'absent_count' => $this->metric('absent_count', __('hr.attendance.metrics.absent_count'), 0),
            'late_count' => $this->metric('late_count', __('hr.attendance.metrics.late_count'), 0),
            'overtime_total' => $this->metric('overtime_total', __('hr.attendance.metrics.overtime_total'), 0),
            'average_work_hours' => $this->metric('average_work_hours', __('hr.attendance.metrics.average_work_hours'), 0.0),
            'attendance_rate' => $this->metric('attendance_rate', __('hr.attendance.metrics.attendance_rate'), 0.0),
            'late_rate' => $this->metric('late_rate', __('hr.attendance.metrics.late_rate'), 0.0),
            'absence_rate' => $this->metric('absence_rate', __('hr.attendance.metrics.absence_rate'), 0.0),
        ];
    }
}
