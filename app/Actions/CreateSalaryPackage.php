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

class CreateSalaryPackage
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
    public function handle(array $data, ?User $actor = null): EmployeeSalaryPackage
    {
        $actor = $this->resolveActor($actor);
        $companyId = $this->resolveCompanyId();

        $this->authorizeSalaryChange($actor, $companyId);
        $this->ensureEmployeeBelongsToCompany((int) $data['employee_id'], $companyId);
        $this->ensureComponentsBelongToCompany($data['items'] ?? [], $companyId);
        $this->ensureNoActiveConflict($data, $companyId);

        return DB::transaction(function () use ($actor, $companyId, $data): EmployeeSalaryPackage {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $salaryPackage = EmployeeSalaryPackage::create([
                ...$data,
                'company_id' => $companyId,
            ]);

            $this->syncItems($salaryPackage, $items, $companyId);

            $this->auditLogger->log(
                action: 'salary_package.created',
                auditable: $salaryPackage,
                newValues: $salaryPackage->load('items')->attributesToArray(),
                user: $actor,
                company: $companyId,
            );

            return $salaryPackage->refresh()->load('items');
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
    private function ensureNoActiveConflict(array $data, int $companyId): void
    {
        $status = (string) ($data['status'] ?? SalaryPackageStatus::Active->value);

        if ($status !== SalaryPackageStatus::Active->value) {
            return;
        }

        $startsOn = (string) $data['effective_from'];
        $endsOn = $data['effective_to'] ?? null;

        $exists = EmployeeSalaryPackage::query()
            ->where('company_id', $companyId)
            ->where('employee_id', $data['employee_id'])
            ->where('status', SalaryPackageStatus::Active->value)
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
