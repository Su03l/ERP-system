<?php

namespace App\Services;

use App\Models\LeaveType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class LeaveTypeIndexQuery
{
    /** @param array<string, mixed> $filters @return LengthAwarePaginator<int, LeaveType> */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return LeaveType::query()
            ->forCurrentCompany()
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->orderBy('name_ar')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }
}
