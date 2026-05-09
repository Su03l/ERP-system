<?php

namespace App\Services;

use App\Models\Company;
use App\Models\EmployeeDocument;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EmployeeDocumentExpiryQuery
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @return Builder<EmployeeDocument>
     */
    public function expiringWithin(int $days = 30, Company|int|null $company = null): Builder
    {
        $today = Carbon::today();

        return $this->baseQuery($company)
            ->whereDate('expiry_date', '>=', $today)
            ->whereDate('expiry_date', '<=', $today->copy()->addDays(max(0, $days)))
            ->orderBy('expiry_date')
            ->orderBy('id');
    }

    /**
     * @return Builder<EmployeeDocument>
     */
    public function expired(Company|int|null $company = null): Builder
    {
        return $this->baseQuery($company)
            ->whereDate('expiry_date', '<', Carbon::today())
            ->orderByDesc('expiry_date')
            ->orderBy('id');
    }

    /**
     * @return Builder<EmployeeDocument>
     */
    public function expiringWithinForCurrentCompany(int $days = 30): Builder
    {
        $companyId = $this->tenantContext->companyId();

        if ($companyId === null) {
            return EmployeeDocument::query()->whereRaw('1 = 0');
        }

        return $this->expiringWithin($days, $companyId);
    }

    /**
     * @return Builder<EmployeeDocument>
     */
    public function expiredForCurrentCompany(): Builder
    {
        $companyId = $this->tenantContext->companyId();

        if ($companyId === null) {
            return EmployeeDocument::query()->whereRaw('1 = 0');
        }

        return $this->expired($companyId);
    }

    /**
     * @return Collection<int, object>
     */
    public function countsByCompany(int $days = 30): Collection
    {
        $today = Carbon::today();
        $expiringUntil = $today->copy()->addDays(max(0, $days));

        return EmployeeDocument::query()
            ->select('company_id')
            ->selectRaw('SUM(CASE WHEN expiry_date < ? THEN 1 ELSE 0 END) as expired_count', [$today->toDateString()])
            ->selectRaw('SUM(CASE WHEN expiry_date >= ? AND expiry_date <= ? THEN 1 ELSE 0 END) as expiring_count', [
                $today->toDateString(),
                $expiringUntil->toDateString(),
            ])
            ->whereNotNull('expiry_date')
            ->groupBy('company_id')
            ->orderBy('company_id')
            ->get();
    }

    /**
     * @return Builder<EmployeeDocument>
     */
    private function baseQuery(Company|int|null $company): Builder
    {
        return EmployeeDocument::query()
            ->with(['company', 'employee'])
            ->when($company !== null, fn (Builder $query): Builder => $query->forCompany($company))
            ->whereNotNull('expiry_date');
    }
}
