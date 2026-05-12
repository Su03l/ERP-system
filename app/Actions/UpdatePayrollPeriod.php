<?php

namespace App\Actions;

use App\Models\PayrollPeriod;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UpdatePayrollPeriod
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    public function handle(PayrollPeriod $period, array $data, ?User $actor = null): PayrollPeriod
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($period);
        Gate::forUser($actor)->authorize('update', $period);

        return DB::transaction(function () use ($actor, $data, $period): PayrollPeriod {
            $oldValues = $period->attributesToArray();
            $period->update($data);

            $this->auditLogger->log('payroll_period.updated', $period, $oldValues, $period->refresh()->attributesToArray(), user: $actor, company: $period->company_id);

            return $period;
        });
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to manage payroll periods.');
        }

        return $actor;
    }

    private function ensureTenant(PayrollPeriod $period): void
    {
        if ($this->tenantContext->companyId() !== $period->company_id) {
            throw new AuthorizationException('Payroll period does not belong to the current company.');
        }
    }
}
