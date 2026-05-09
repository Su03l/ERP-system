<?php

namespace App\Services;

use App\Models\JobTitle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class JobTitleIndexQuery
{
    /** @param array<string,mixed> $filters @return LengthAwarePaginator<int, JobTitle> */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return JobTitle::query()
            ->forCurrentCompany()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(fn (Builder $query) => $query->where('name_ar', 'like', "%{$search}%")->orWhere('name_en', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }
}
