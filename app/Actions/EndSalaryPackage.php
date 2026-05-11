<?php

namespace App\Actions;

use App\Enums\SalaryPackageStatus;
use App\Models\EmployeeSalaryPackage;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EndSalaryPackage
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @throws AuthorizationException
     */
    public function handle(EmployeeSalaryPackage $salaryPackage, string|\DateTimeInterface|null $effectiveTo = null, ?User $actor = null): EmployeeSalaryPackage
    {
        $actor = $this->resolveActor($actor);
        $companyId = $this->resolveCompanyId();

        $this->authorizeSalaryChange($actor, $companyId);
        $this->ensurePackageBelongsToCompany($salaryPackage, $companyId);

        return DB::transaction(function () use ($actor, $companyId, $effectiveTo, $salaryPackage): EmployeeSalaryPackage {
            $oldValues = $salaryPackage->attributesToArray();

            $salaryPackage->forceFill([
                'effective_to' => $effectiveTo ?? now()->toDateString(),
                'status' => SalaryPackageStatus::Inactive,
            ])->save();

            $this->auditLogger->log(
                action: 'salary_package.ended',
                auditable: $salaryPackage,
                oldValues: $oldValues,
                newValues: $salaryPackage->refresh()->attributesToArray(),
                user: $actor,
                company: $companyId,
            );

            return $salaryPackage;
        });
    }

    /**
     * @throws AuthorizationException
     */
    private function resolveActor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to manage salary packages.');
        }

        return $actor;
    }

    /**
     * @throws AuthorizationException
     */
    private function resolveCompanyId(): int
    {
        $companyId = $this->tenantContext->companyId();

        if ($companyId === null) {
            throw new AuthorizationException('A current company is required to manage salary packages.');
        }

        return $companyId;
    }

    /**
     * @throws AuthorizationException
     */
    private function authorizeSalaryChange(User $actor, int $companyId): void
    {
        if (! $actor->hasPermission('employees.update_salary', $companyId)) {
            throw new AuthorizationException('You are not authorized to manage salary packages.');
        }
    }

    /**
     * @throws AuthorizationException
     */
    private function ensurePackageBelongsToCompany(EmployeeSalaryPackage $salaryPackage, int $companyId): void
    {
        if ($salaryPackage->company_id !== $companyId) {
            throw new AuthorizationException('Salary package does not belong to the current company.');
        }
    }
}
