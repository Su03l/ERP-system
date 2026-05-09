<?php

namespace App\Actions;

use App\Models\Department;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UpdateDepartment
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
    public function handle(Department $department, array $data, ?User $actor = null): Department
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to update departments.');
        }

        Gate::forUser($actor)->authorize('update', $department);
        $this->ensureDepartmentBelongsToCurrentCompany($department);

        return DB::transaction(function () use ($actor, $data, $department): Department {
            $oldValues = $department->attributesToArray();

            $department->fill($data);
            $department->save();

            $this->auditLogger->log(
                action: 'department.updated',
                auditable: $department,
                oldValues: $oldValues,
                newValues: $department->refresh()->attributesToArray(),
                user: $actor,
                company: $department->company_id,
            );

            return $department;
        });
    }

    /**
     * @throws AuthorizationException
     */
    private function ensureDepartmentBelongsToCurrentCompany(Department $department): void
    {
        if ($this->tenantContext->companyId() !== $department->company_id) {
            throw new AuthorizationException('Department does not belong to the current company.');
        }
    }
}
