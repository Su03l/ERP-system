<?php

namespace App\Services;

use App\Models\LeaveRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class LeaveRequestIndexQuery
{
    /** @param array<string, mixed> $filters @return LengthAwarePaginator<int, LeaveRequest> */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return LeaveRequest::query()
            ->forCurrentCompany()
            ->with(['employee', 'leaveType', 'workflowInstance', 'approvedBy'])
            ->when($filters['employee_id'] ?? null, fn (Builder $query, int|string $employeeId): Builder => $query->where('employee_id', $employeeId))
            ->when($filters['leave_type_id'] ?? null, fn (Builder $query, int|string $leaveTypeId): Builder => $query->where('leave_type_id', $leaveTypeId))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $dateFrom): Builder => $query->whereDate('start_date', '>=', $dateFrom))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $dateTo): Builder => $query->whereDate('end_date', '<=', $dateTo))
            ->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }
}
