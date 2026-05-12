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

class CreateSalaryComponent
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    public function handle(array $data, ?User $actor = null): SalaryComponent
    {
        $actor = $this->actor($actor);
        Gate::forUser($actor)->authorize('create', SalaryComponent::class);
        $companyId = $this->companyId();

        return DB::transaction(function () use ($actor, $companyId, $data): SalaryComponent {
            $salaryComponent = SalaryComponent::create([...$data, 'company_id' => $companyId]);

            $this->auditLogger->log('salary_component.created', $salaryComponent, newValues: $salaryComponent->attributesToArray(), user: $actor, company: $companyId);

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

    private function companyId(): int
    {
        $companyId = $this->tenantContext->companyId();

        if ($companyId === null) {
            throw new AuthorizationException('A current company is required.');
        }

        return $companyId;
    }
}
