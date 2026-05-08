<?php

namespace App\Actions;

use App\Models\Employee;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class AssignEmployeeToUser
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @throws AuthorizationException
     */
    public function handle(Employee $employee, User $user, ?User $actor = null): Employee
    {
        $companyId = $this->tenantContext->companyId();

        if ($companyId === null || $employee->company_id !== $companyId || $user->company_id !== $companyId) {
            throw new AuthorizationException('Employee and user must belong to the current company.');
        }

        return DB::transaction(function () use ($actor, $employee, $user): Employee {
            $oldValues = $employee->attributesToArray();

            $employee->forceFill(['user_id' => $user->id])->save();

            $this->auditLogger->log(
                action: 'employee.assigned_to_user',
                auditable: $employee,
                oldValues: $oldValues,
                newValues: $employee->refresh()->attributesToArray(),
                user: $actor,
                company: $employee->company_id,
            );

            return $employee;
        });
    }
}
