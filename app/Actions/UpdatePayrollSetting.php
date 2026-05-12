<?php

namespace App\Actions;

use App\Models\PayrollSetting;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UpdatePayrollSetting
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    public function handle(PayrollSetting $setting, array $data, ?User $actor = null): PayrollSetting
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($setting);
        Gate::forUser($actor)->authorize('update', $setting);

        return DB::transaction(function () use ($actor, $data, $setting): PayrollSetting {
            $oldValues = $setting->attributesToArray();
            $setting->update($data);

            $this->auditLogger->log('payroll_setting.updated', $setting, $oldValues, $setting->refresh()->attributesToArray(), user: $actor, company: $setting->company_id);

            return $setting;
        });
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to manage payroll settings.');
        }

        return $actor;
    }

    private function ensureTenant(PayrollSetting $setting): void
    {
        if ($this->tenantContext->companyId() !== $setting->company_id) {
            throw new AuthorizationException('Payroll setting does not belong to the current company.');
        }
    }
}
