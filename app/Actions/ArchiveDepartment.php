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

class ArchiveDepartment
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @throws AuthorizationException
     */
    public function handle(Department $department, ?User $actor = null): Department
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to archive departments.');
        }

        Gate::forUser($actor)->authorize('delete', $department);
        $this->ensureDepartmentBelongsToCurrentCompany($department);

        return DB::transaction(function () use ($actor, $department): Department {
            $oldValues = $department->attributesToArray();

            $department->delete();

            $this->auditLogger->log(
                action: 'department.archived',
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
