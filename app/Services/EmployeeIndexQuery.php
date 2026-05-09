<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EmployeeIndexQuery
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Employee>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return Employee::query()
            ->forCurrentCompany()
            ->with(['department', 'jobTitle', 'manager'])
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('employee_number', 'like', "%{$search}%")
                        ->orWhere('first_name_ar', 'like', "%{$search}%")
                        ->orWhere('last_name_ar', 'like', "%{$search}%")
                        ->orWhere('first_name_en', 'like', "%{$search}%")
                        ->orWhere('last_name_en', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($filters['department_id'] ?? null, fn (Builder $query, int|string $departmentId): Builder => $query->where('department_id', $departmentId))
            ->when($filters['job_title_id'] ?? null, fn (Builder $query, int|string $jobTitleId): Builder => $query->where('job_title_id', $jobTitleId))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('employment_status', $status))
            ->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }
}
