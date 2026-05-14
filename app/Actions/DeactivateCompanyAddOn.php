<?php

namespace App\Actions;

use App\Enums\CompanyAddOnStatus;
use App\Enums\CompanyModule;
use App\Models\Company;
use App\Models\CompanyAddOn;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DeactivateCompanyAddOn
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(CompanyAddOn $companyAddOn, ?User $actor = null, ?string $reason = null): CompanyAddOn
    {
        return DB::transaction(function () use ($actor, $companyAddOn, $reason): CompanyAddOn {
            $companyAddOn->loadMissing('addOn', 'company');
            $oldValues = $companyAddOn->attributesToArray();

            $companyAddOn->forceFill([
                'status' => CompanyAddOnStatus::Inactive,
                'ends_at' => Carbon::now(),
            ])->save();

            $this->disableMappedModuleIfUnused($companyAddOn->company, $companyAddOn);

            $this->auditLogger->log(
                action: 'company_add_on.deactivated',
                auditable: $companyAddOn,
                oldValues: $oldValues,
                newValues: $companyAddOn->refresh()->attributesToArray(),
                metadata: ['reason' => $reason, 'feature_key' => $companyAddOn->addOn->feature_key],
                user: $actor,
                company: $companyAddOn->company_id,
            );

            return $companyAddOn->load('company', 'addOn');
        });
    }

    private function disableMappedModuleIfUnused(Company $company, CompanyAddOn $companyAddOn): void
    {
        $featureKey = $companyAddOn->addOn->feature_key;

        if (! in_array($featureKey, CompanyModule::keys(), true)) {
            return;
        }

        $stillEnabled = CompanyAddOn::query()
            ->where('company_id', $company->id)
            ->whereKeyNot($companyAddOn->id)
            ->where('status', CompanyAddOnStatus::Active->value)
            ->whereHas('addOn', fn ($query) => $query->where('feature_key', $featureKey))
            ->exists();

        if ($stillEnabled) {
            return;
        }

        $settings = $company->settings ?? [];
        $settings['enabled_modules'] = array_values(array_diff($settings['enabled_modules'] ?? [], [$featureKey]));
        $company->forceFill(['settings' => $settings])->save();
    }
}
