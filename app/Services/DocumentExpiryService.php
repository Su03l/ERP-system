<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyDocument;
use App\Models\EmployeeDocument;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DocumentExpiryService
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @return Collection<string, EloquentCollection<int, EmployeeDocument|CompanyDocument>>
     */
    public function expiringWithin(int $days = 30, Company|int|null $company = null): Collection
    {
        $today = Carbon::today();
        $until = $today->copy()->addDays(max(0, $days));

        return collect([
            'employee_documents' => $this->employeeBase($company)
                ->whereDate('expiry_date', '>=', $today)
                ->whereDate('expiry_date', '<=', $until)
                ->orderBy('expiry_date')
                ->get(),
            'company_documents' => $this->companyBase($company)
                ->whereDate('expiry_date', '>=', $today)
                ->whereDate('expiry_date', '<=', $until)
                ->orderBy('expiry_date')
                ->get(),
        ]);
    }

    /**
     * @return Collection<string, EloquentCollection<int, EmployeeDocument|CompanyDocument>>
     */
    public function expired(Company|int|null $company = null): Collection
    {
        $today = Carbon::today();

        return collect([
            'employee_documents' => $this->employeeBase($company)
                ->whereDate('expiry_date', '<', $today)
                ->orderByDesc('expiry_date')
                ->get(),
            'company_documents' => $this->companyBase($company)
                ->whereDate('expiry_date', '<', $today)
                ->orderByDesc('expiry_date')
                ->get(),
        ]);
    }

    /**
     * @return Collection<string, EloquentCollection<int, EmployeeDocument|CompanyDocument>>
     */
    public function expiringWithinForCurrentCompany(int $days = 30): Collection
    {
        return $this->expiringWithin($days, $this->tenantContext->companyId() ?? 0);
    }

    /**
     * @return Collection<string, EloquentCollection<int, EmployeeDocument|CompanyDocument>>
     */
    public function expiredForCurrentCompany(): Collection
    {
        return $this->expired($this->tenantContext->companyId() ?? 0);
    }

    /**
     * @return Collection<int, array{company_id: int, expired_count: int, expiring_count: int}>
     */
    public function countsByCompany(int $days = 30): Collection
    {
        $today = Carbon::today();
        $until = $today->copy()->addDays(max(0, $days));
        $counts = [];

        foreach ([EmployeeDocument::class, CompanyDocument::class] as $modelClass) {
            $modelClass::query()
                ->whereNotNull('expiry_date')
                ->get(['company_id', 'expiry_date'])
                ->each(function (EmployeeDocument|CompanyDocument $document) use (&$counts, $today, $until): void {
                    $companyId = (int) $document->company_id;
                    $counts[$companyId] ??= ['company_id' => $companyId, 'expired_count' => 0, 'expiring_count' => 0];

                    if ($document->expiry_date->lt($today)) {
                        $counts[$companyId]['expired_count']++;
                    } elseif ($document->expiry_date->betweenIncluded($today, $until)) {
                        $counts[$companyId]['expiring_count']++;
                    }
                });
        }

        return collect(array_values($counts))->sortBy('company_id')->values();
    }

    /**
     * @return Builder<EmployeeDocument>
     */
    private function employeeBase(Company|int|null $company): Builder
    {
        return EmployeeDocument::query()
            ->with(['company', 'employee'])
            ->when($company !== null, fn ($query) => $query->forCompany($company))
            ->whereNotNull('expiry_date');
    }

    /**
     * @return Builder<CompanyDocument>
     */
    private function companyBase(Company|int|null $company): Builder
    {
        return CompanyDocument::query()
            ->with(['company'])
            ->when($company !== null, fn ($query) => $query->forCompany($company))
            ->whereNotNull('expiry_date');
    }
}
