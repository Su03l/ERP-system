<?php

namespace App\Actions;

use App\Models\SalaryComponent;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UpdateSalaryComponent
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    public function handle(SalaryComponent $salaryComponent, array $data, ?User $actor = null): SalaryComponent
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($salaryComponent);
        Gate::forUser($actor)->authorize('update', $salaryComponent);

        return DB::transaction(function () use ($actor, $data, $salaryComponent): SalaryComponent {
            $oldValues = $salaryComponent->attributesToArray();
            $salaryComponent->update($data);

            $this->auditLogger->log('salary_component.updated', $salaryComponent, $oldValues, $salaryComponent->refresh()->attributesToArray(), user: $actor, company: $salaryComponent->company_id);

            return $salaryComponent;
        });
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to manage salary components.');
        }

        return $actor;
    }

    private function ensureTenant(SalaryComponent $salaryComponent): void
    {
        if ($this->tenantContext->companyId() !== $salaryComponent->company_id) {
            throw new AuthorizationException('Salary component does not belong to the current company.');
        }
    }
}
