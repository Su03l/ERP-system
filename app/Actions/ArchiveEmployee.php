<?php

namespace App\Actions;

use App\Models\Employee;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class ArchiveEmployee
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @throws AuthorizationException
     */
    public function handle(Employee $employee, ?User $actor = null): Employee
    {
        $this->ensureEmployeeBelongsToCurrentCompany($employee);

        return DB::transaction(function () use ($actor, $employee): Employee {
            $oldValues = $employee->attributesToArray();

            $employee->delete();

            $this->auditLogger->log(
                action: 'employee.archived',
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
