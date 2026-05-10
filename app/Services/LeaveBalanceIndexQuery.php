<?php

namespace App\Services;

use App\Models\LeaveBalance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class LeaveBalanceIndexQuery
{
    /** @param array<string, mixed> $filters @return LengthAwarePaginator<int, LeaveBalance> */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return LeaveBalance::query()
            ->forCurrentCompany()
            ->with(['employee', 'leaveType'])
            ->when($filters['employee_id'] ?? null, fn (Builder $query, int|string $employeeId): Builder => $query->where('employee_id', $employeeId))
            ->when($filters['leave_type_id'] ?? null, fn (Builder $query, int|string $leaveTypeId): Builder => $query->where('leave_type_id', $leaveTypeId))
            ->when($filters['year'] ?? null, fn (Builder $query, int|string $year): Builder => $query->where('year', $year))
            ->latest('year')
            ->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }
}
