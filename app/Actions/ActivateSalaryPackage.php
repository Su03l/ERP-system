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
use Illuminate\Validation\ValidationException;

class ActivateSalaryPackage
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function handle(EmployeeSalaryPackage $salaryPackage, ?User $actor = null): EmployeeSalaryPackage
    {
        $actor = $this->resolveActor($actor);
        $companyId = $this->resolveCompanyId();

        $this->authorizeSalaryChange($actor, $companyId);
        $this->ensurePackageBelongsToCompany($salaryPackage, $companyId);
        $this->ensureNoActiveConflict($salaryPackage, $companyId);

        return DB::transaction(function () use ($actor, $companyId, $salaryPackage): EmployeeSalaryPackage {
            $oldValues = $salaryPackage->attributesToArray();

            $salaryPackage->forceFill([
                'status' => SalaryPackageStatus::Active,
            ])->save();

            $this->auditLogger->log(
                action: 'salary_package.activated',
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
        if (! $actor->hasPermission('salary_packages.update', $companyId)) {
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

    /**
     * @throws ValidationException
     */
    private function ensureNoActiveConflict(EmployeeSalaryPackage $salaryPackage, int $companyId): void
    {
        $startsOn = $salaryPackage->effective_from?->toDateString();
        $endsOn = $salaryPackage->effective_to?->toDateString();

        $exists = EmployeeSalaryPackage::query()
            ->where('company_id', $companyId)
            ->where('employee_id', $salaryPackage->employee_id)
            ->where('status', SalaryPackageStatus::Active->value)
            ->whereKeyNot($salaryPackage->id)
            ->whereDate('effective_from', '<=', $endsOn ?: '9999-12-31')
            ->where(function ($query) use ($startsOn): void {
                $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $startsOn);
            })
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'effective_from' => __('validation.custom.salary_packages.active_conflict'),
            ]);
        }
    }
}
