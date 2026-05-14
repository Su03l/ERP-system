<?php

namespace App\Services;

use App\Enums\CompanyAddOnStatus;
use App\Enums\CompanyModule;
use App\Models\AddOn;
use App\Models\Company;
use App\Models\CompanyAddOn;

class CheckCompanyAddOnAccess
{
    public function __construct(private readonly CompanyModuleService $companyModuleService) {}

    public function handle(Company $company, AddOn|string $addOnOrFeature): bool
    {
        $featureKey = $addOnOrFeature instanceof AddOn ? $addOnOrFeature->feature_key : $addOnOrFeature;
        $addOnId = $addOnOrFeature instanceof AddOn ? $addOnOrFeature->id : null;

        $hasActiveAddOn = CompanyAddOn::query()
            ->where('company_id', $company->id)
            ->where('status', CompanyAddOnStatus::Active->value)
            ->where(function ($query) use ($addOnId, $featureKey): void {
                if ($addOnId !== null) {
                    $query->where('add_on_id', $addOnId);
                }

                if ($featureKey !== null) {
                    $query->orWhereHas('addOn', fn ($query) => $query->where('feature_key', $featureKey));
                }
            })
            ->where(function ($query): void {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->exists();

        if ($hasActiveAddOn) {
            return true;
        }

        if ($featureKey !== null && in_array($featureKey, CompanyModule::keys(), true)) {
            return $this->companyModuleService->isEnabled($company, $featureKey);
        }

        return false;
    }
}
