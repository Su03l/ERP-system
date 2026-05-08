<?php

namespace App\Actions;

use App\Models\Employee;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class CreateEmployee
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
    public function handle(array $data, ?User $actor = null): Employee
    {
        $companyId = $this->tenantContext->companyId();

        if ($companyId === null) {
            throw new AuthorizationException('A current company is required to create employees.');
        }

        return DB::transaction(function () use ($actor, $companyId, $data): Employee {
            $employee = Employee::create([
                ...$data,
                'company_id' => $companyId,
            ]);

            $this->auditLogger->log(
                action: 'employee.created',
                auditable: $employee,
                newValues: $employee->attributesToArray(),
                user: $actor,
                company: $companyId,
            );

            return $employee;
        });
    }
}
