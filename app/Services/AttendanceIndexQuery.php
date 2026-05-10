<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AttendanceIndexQuery
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, AttendanceRecord>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return AttendanceRecord::query()
            ->forCurrentCompany()
            ->with(['employee.department', 'employee.jobTitle'])
            ->when($filters['employee_id'] ?? null, fn (Builder $query, int|string $employeeId): Builder => $query->where('employee_id', $employeeId))
            ->when($filters['department_id'] ?? null, function (Builder $query, int|string $departmentId): void {
                $query->whereHas('employee', fn (Builder $employeeQuery): Builder => $employeeQuery->where('department_id', $departmentId));
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $dateFrom): Builder => $query->whereDate('attendance_date', '>=', $dateFrom))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $dateTo): Builder => $query->whereDate('attendance_date', '<=', $dateTo))
            ->latest('attendance_date')
            ->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }
}
