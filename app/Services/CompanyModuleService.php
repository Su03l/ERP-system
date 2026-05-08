<?php

namespace App\Services;

use App\Enums\CompanyModule;
use App\Models\Company;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class CompanyModuleService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    public function isEnabled(Company $company, CompanyModule|string $module): bool
    {
        $moduleKey = $module instanceof CompanyModule ? $module->value : $module;

        return in_array($moduleKey, $this->enabledModules($company), true);
    }

    /**
     * @return array<int, string>
     */
    public function enabledModules(Company $company): array
    {
        return array_values(array_intersect(
            $company->settings['enabled_modules'] ?? [],
            CompanyModule::keys(),
        ));
    }

    /**
     * @param  array<int, CompanyModule|string>  $modules
     *
     * @throws AuthorizationException
     */
    public function sync(Company $company, array $modules): Company
    {
        $this->ensureCurrentTenant($company);

        return DB::transaction(function () use ($company, $modules): Company {
            $oldValues = [
                'enabled_modules' => $this->enabledModules($company),
            ];

            $settings = $company->settings ?? [];
            $settings['enabled_modules'] = $this->normalizeModules($modules);
            $company->settings = $settings;
            $company->save();

            $this->auditLogger->log(
                action: 'company.modules.updated',
                auditable: $company,
                oldValues: $oldValues,
                newValues: ['enabled_modules' => $settings['enabled_modules']],
                company: $company,
            );

            return $company->refresh();
        });
    }

    /**
     * @param  array<int, CompanyModule|string>  $modules
     * @return array<int, string>
     */
    private function normalizeModules(array $modules): array
    {
        $moduleKeys = array_map(
            fn (CompanyModule|string $module): string => $module instanceof CompanyModule ? $module->value : $module,
            $modules,
        );

        return array_values(array_intersect(array_unique($moduleKeys), CompanyModule::keys()));
    }

    private function ensureCurrentTenant(Company $company): void
    {
        if ($this->tenantContext->companyId() !== $company->id) {
            throw new AuthorizationException('The selected company is not the current tenant.');
        }
    }
}
