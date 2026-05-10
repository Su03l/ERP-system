<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AttendanceExportQuery
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array{entity_type: string, module_key: string, columns: array<int, array{key: string, label: string}>, rows: array<int, array<string, mixed>>}
     *
     * @throws AuthorizationException
     */
    public function export(array $filters = [], ?User $actor = null): array
    {
        $actor ??= Auth::user();
        $companyId = $this->tenantContext->companyId();

        if (! $actor instanceof User || $companyId === null || ! $actor->hasPermission('attendance.export', $companyId)) {
            throw new AuthorizationException('You are not authorized to export attendance records.');
        }

        $rows = $this->query($filters, $companyId)
            ->get()
            ->map(fn (AttendanceRecord $attendanceRecord): array => $this->mapAttendanceRecord($attendanceRecord))
            ->values()
            ->all();

        return [
            'entity_type' => 'attendance_records',
            'module_key' => 'attendance',
            'columns' => $this->columns(),
            'rows' => $rows,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<AttendanceRecord>
     */
    private function query(array $filters, int $companyId): Builder
    {
        return AttendanceRecord::query()
            ->forCompany($companyId)
            ->with(['employee.department', 'employee.jobTitle'])
            ->when($filters['employee_id'] ?? null, fn (Builder $query, int|string $employeeId): Builder => $query->where('employee_id', $employeeId))
            ->when($filters['department_id'] ?? null, function (Builder $query, int|string $departmentId): void {
                $query->whereHas('employee', fn (Builder $employeeQuery): Builder => $employeeQuery->where('department_id', $departmentId));
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $dateFrom): Builder => $query->whereDate('attendance_date', '>=', $dateFrom))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $dateTo): Builder => $query->whereDate('attendance_date', '<=', $dateTo))
            ->orderBy('attendance_date')
            ->orderBy('employee_id')
            ->orderBy('id');
    }

    /**
     * @return array<string, mixed>
     */
    private function mapAttendanceRecord(AttendanceRecord $attendanceRecord): array
    {
        $employee = $attendanceRecord->employee;

        return [
            'employee_number' => $employee?->employee_number,
            'employee_name_ar' => $employee ? trim("{$employee->first_name_ar} {$employee->last_name_ar}") : null,
            'employee_name_en' => $employee ? $this->localizedName($employee->first_name_en, $employee->last_name_en) : null,
            'department' => $employee?->department?->name_ar ?? $employee?->department?->name_en,
            'job_title' => $employee?->jobTitle?->name_ar ?? $employee?->jobTitle?->name_en,
            'attendance_date' => $attendanceRecord->attendance_date?->toDateString(),
            'clock_in_at' => $attendanceRecord->clock_in_at?->toDateTimeString(),
            'clock_out_at' => $attendanceRecord->clock_out_at?->toDateTimeString(),
            'status' => $attendanceRecord->status?->value,
            'status_label' => $attendanceRecord->status?->label(),
            'source' => $attendanceRecord->source?->value,
            'source_label' => $attendanceRecord->source?->label(),
            'late_minutes' => $attendanceRecord->late_minutes,
            'overtime_minutes' => $attendanceRecord->overtime_minutes,
            'total_work_minutes' => $attendanceRecord->total_work_minutes,
            'notes' => $attendanceRecord->notes,
        ];
    }

    private function localizedName(?string $firstName, ?string $lastName): ?string
    {
        $name = trim("{$firstName} {$lastName}");

        return $name === '' ? null : $name;
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    private function columns(): array
    {
        return [
            ['key' => 'employee_number', 'label' => __('hr.attendance.export.columns.employee_number')],
            ['key' => 'employee_name_ar', 'label' => __('hr.attendance.export.columns.employee_name_ar')],
            ['key' => 'employee_name_en', 'label' => __('hr.attendance.export.columns.employee_name_en')],
            ['key' => 'department', 'label' => __('hr.attendance.export.columns.department')],
            ['key' => 'job_title', 'label' => __('hr.attendance.export.columns.job_title')],
            ['key' => 'attendance_date', 'label' => __('hr.attendance.export.columns.attendance_date')],
            ['key' => 'clock_in_at', 'label' => __('hr.attendance.export.columns.clock_in_at')],
            ['key' => 'clock_out_at', 'label' => __('hr.attendance.export.columns.clock_out_at')],
            ['key' => 'status_label', 'label' => __('hr.attendance.export.columns.status')],
            ['key' => 'source_label', 'label' => __('hr.attendance.export.columns.source')],
            ['key' => 'late_minutes', 'label' => __('hr.attendance.export.columns.late_minutes')],
            ['key' => 'overtime_minutes', 'label' => __('hr.attendance.export.columns.overtime_minutes')],
            ['key' => 'total_work_minutes', 'label' => __('hr.attendance.export.columns.total_work_minutes')],
            ['key' => 'notes', 'label' => __('hr.attendance.export.columns.notes')],
        ];
    }
}
