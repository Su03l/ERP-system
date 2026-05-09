<?php

namespace App\Actions;

use App\Models\Employee;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UpdateEmployee
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function handle(Employee $employee, array $data, ?User $actor = null): Employee
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to update employees.');
        }

        Gate::forUser($actor)->authorize('update', $employee);

        if (array_key_exists('basic_salary', $data)) {
            Gate::forUser($actor)->authorize('updateSalary', $employee);
        }

        $this->ensureEmployeeBelongsToCurrentCompany($employee);

        return DB::transaction(function () use ($actor, $data, $employee): Employee {
            $oldValues = $employee->attributesToArray();

            $employee->fill($data);
            $employee->save();

            $this->auditLogger->log(
                action: 'employee.updated',
                auditable: $employee,
                oldValues: $oldValues,
                newValues: $employee->refresh()->attributesToArray(),
                user: $actor,
                company: $employee->company_id,
            );

            return $employee;
        });
    }

    /**
     * @throws AuthorizationException
     */
    private function ensureEmployeeBelongsToCurrentCompany(Employee $employee): void
    {
        if ($this->tenantContext->companyId() !== $employee->company_id) {
            throw new AuthorizationException('Employee does not belong to the current company.');
        }
    }
}
