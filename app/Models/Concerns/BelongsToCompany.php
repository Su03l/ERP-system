<?php

namespace App\Models\Concerns;

use App\Models\Company;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCompany
{
    /**
     * Get the company that owns this model.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope the query to records owned by a company.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForCompany(Builder $query, Company|int $company): Builder
    {
        return $query->where($this->getTable().'.company_id', $company instanceof Company ? $company->id : $company);
    }

    /**
     * Scope the query to records owned by the current tenant company.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForCurrentCompany(Builder $query): Builder
    {
        $companyId = app(TenantContext::class)->companyId();

        if ($companyId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($this->getTable().'.company_id', $companyId);
    }
}
