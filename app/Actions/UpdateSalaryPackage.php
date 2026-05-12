<?php

namespace App\Actions;

use App\Enums\SalaryPackageStatus;
use App\Models\Employee;
use App\Models\EmployeeSalaryPackage;
use App\Models\SalaryComponent;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateSalaryPackage
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function handle(EmployeeSalaryPackage $salaryPackage, array $data, ?User $actor = null): EmployeeSalaryPackage
    {
        $actor = $this->resolveActor($actor);
        $companyId = $this->resolveCompanyId();

        $this->authorizeSalaryChange($actor, $companyId);
        $this->ensurePackageBelongsToCompany($salaryPackage, $companyId);
        $this->ensureEmployeeBelongsToCompany((int) ($data['employee_id'] ?? $salaryPackage->employee_id), $companyId);
        $this->ensureComponentsBelongToCompany($data['items'] ?? [], $companyId);
        $this->ensureNoActiveConflict($salaryPackage, $data, $companyId);

        return DB::transaction(function () use ($actor, $companyId, $data, $salaryPackage): EmployeeSalaryPackage {
            $oldValues = $salaryPackage->load('items')->attributesToArray();
            $items = $data['items'] ?? null;
            unset($data['items']);

            $salaryPackage->fill($data);
            $salaryPackage->save();

            if (is_array($items)) {
                $salaryPackage->items()->delete();
                $this->syncItems($salaryPackage, $items, $companyId);
            }

            $this->auditLogger->log(
                action: 'salary_package.updated',
                auditable: $salaryPackage,
                oldValues: $oldValues,
                newValues: $salaryPackage->refresh()->load('items')->attributesToArray(),
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
     * @throws AuthorizationException
     */
    private function ensureEmployeeBelongsToCompany(int $employeeId, int $companyId): void
    {
        if (! Employee::query()->whereKey($employeeId)->where('company_id', $companyId)->exists()) {
            throw new AuthorizationException('Employee does not belong to the current company.');
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     *
     * @throws AuthorizationException
     */
    private function ensureComponentsBelongToCompany(array $items, int $companyId): void
    {
        $componentIds = collect($items)
            ->pluck('salary_component_id')
            ->filter()
            ->unique()
            ->values();

        if ($componentIds->isEmpty()) {
            return;
        }

        $validCount = SalaryComponent::query()
            ->where('company_id', $companyId)
            ->whereKey($componentIds)
            ->count();

        if ($validCount !== $componentIds->count()) {
            throw new AuthorizationException('Salary component does not belong to the current company.');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    private function ensureNoActiveConflict(EmployeeSalaryPackage $salaryPackage, array $data, int $companyId): void
    {
        $status = (string) ($data['status'] ?? $salaryPackage->status->value);

        if ($status !== SalaryPackageStatus::Active->value) {
            return;
        }

        $employeeId = (int) ($data['employee_id'] ?? $salaryPackage->employee_id);
        $startsOn = (string) ($data['effective_from'] ?? $salaryPackage->effective_from?->toDateString());
        $endsOn = $data['effective_to'] ?? $salaryPackage->effective_to?->toDateString();

        $exists = EmployeeSalaryPackage::query()
            ->where('company_id', $companyId)
            ->where('employee_id', $employeeId)
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

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncItems(EmployeeSalaryPackage $salaryPackage, array $items, int $companyId): void
    {
        foreach ($items as $item) {
            $salaryPackage->items()->create([
                ...$item,
                'company_id' => $companyId,
            ]);
        }
    }
}
