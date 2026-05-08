<?php

namespace App\Services;

use App\Models\Company;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CompanySettingsService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $settings
     *
     * @throws AuthorizationException
     */
    public function update(Company $company, array $settings): Company
    {
        $this->ensureCurrentTenant($company);

        return DB::transaction(function () use ($company, $settings): Company {
            $oldValues = $this->snapshot($company);
            $companySettings = $company->settings ?? [];

            $company->fill(Arr::only($settings, [
                'name',
                'legal_name',
                'email',
                'phone',
                'locale',
                'timezone',
                'currency',
            ]));

            foreach (['date_preference', 'working_days', 'branding', 'notification_preferences'] as $key) {
                if (array_key_exists($key, $settings)) {
                    $companySettings[$key] = $settings[$key];
                }
            }

            $company->settings = $companySettings;
            $company->save();

            $this->auditLogger->log(
                action: 'company.settings.updated',
                auditable: $company,
                oldValues: $oldValues,
                newValues: $this->snapshot($company),
                company: $company,
            );

            return $company->refresh();
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function read(Company $company): array
    {
        $this->ensureCurrentTenant($company);

        return $this->snapshot($company);
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(Company $company): array
    {
        return [
            'name' => $company->name,
            'legal_name' => $company->legal_name,
            'email' => $company->email,
            'phone' => $company->phone,
            'locale' => $company->locale,
            'timezone' => $company->timezone,
            'currency' => $company->currency,
            'settings' => $company->settings ?? [],
        ];
    }

    private function ensureCurrentTenant(Company $company): void
    {
        if ($this->tenantContext->companyId() !== $company->id) {
            throw new AuthorizationException('The selected company is not the current tenant.');
        }
    }
}
