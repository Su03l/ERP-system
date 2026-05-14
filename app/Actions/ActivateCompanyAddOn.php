<?php

namespace App\Actions;

use App\Enums\CompanyAddOnStatus;
use App\Enums\CompanyModule;
use App\Models\AddOn;
use App\Models\Company;
use App\Models\CompanyAddOn;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ActivateCompanyAddOn
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  array{starts_at?: Carbon|string|null, ends_at?: Carbon|string|null, metadata?: array<string, mixed>}  $data
     */
    public function handle(Company $company, AddOn $addOn, array $data = [], ?User $actor = null): CompanyAddOn
    {
        return DB::transaction(function () use ($actor, $addOn, $company, $data): CompanyAddOn {
            $companyAddOn = CompanyAddOn::query()
                ->where('company_id', $company->id)
                ->where('add_on_id', $addOn->id)
                ->first();
            $oldValues = $companyAddOn?->attributesToArray();

            $companyAddOn ??= new CompanyAddOn([
                'company_id' => $company->id,
                'add_on_id' => $addOn->id,
            ]);

            $companyAddOn->forceFill([
                'status' => CompanyAddOnStatus::Active,
                'starts_at' => isset($data['starts_at']) ? Carbon::parse($data['starts_at']) : ($companyAddOn->starts_at ?? now()),
                'ends_at' => isset($data['ends_at']) ? Carbon::parse($data['ends_at']) : null,
                'metadata' => array_replace($companyAddOn->metadata ?? [], $data['metadata'] ?? []),
            ])->save();

            $this->enableMappedModule($company, $addOn);

            $this->auditLogger->log(
                action: 'company_add_on.activated',
                auditable: $companyAddOn,
                oldValues: $oldValues,
                newValues: $companyAddOn->refresh()->attributesToArray(),
                metadata: ['add_on_id' => $addOn->id, 'feature_key' => $addOn->feature_key],
                user: $actor,
                company: $company,
            );

            return $companyAddOn->load('company', 'addOn');
        });
    }

    private function enableMappedModule(Company $company, AddOn $addOn): void
    {
        if (! in_array($addOn->feature_key, CompanyModule::keys(), true)) {
            return;
        }

        $settings = $company->settings ?? [];
        $modules = array_values(array_unique([
            ...($settings['enabled_modules'] ?? []),
            $addOn->feature_key,
        ]));
        $settings['enabled_modules'] = array_values(array_intersect($modules, CompanyModule::keys()));
        $company->forceFill(['settings' => $settings])->save();
    }
}
