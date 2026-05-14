<?php

namespace App\Actions;

use App\Models\CompanyAddOn;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class UpdateCompanyAddOn
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /** @param array<string, mixed> $data */
    public function handle(CompanyAddOn $companyAddOn, array $data, ?User $actor = null): CompanyAddOn
    {
        return DB::transaction(function () use ($actor, $companyAddOn, $data): CompanyAddOn {
            $oldValues = $companyAddOn->attributesToArray();
            $companyAddOn->update($data);

            $this->auditLogger->log(
                action: 'company_add_on.updated',
                auditable: $companyAddOn,
                oldValues: $oldValues,
                newValues: $companyAddOn->refresh()->attributesToArray(),
                user: $actor,
                company: $companyAddOn->company_id,
            );

            return $companyAddOn->load(['company', 'addOn']);
        });
    }
}
