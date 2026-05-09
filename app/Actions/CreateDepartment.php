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

class CreateDepartment
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
    public function handle(array $data, ?User $actor = null): Department
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to create departments.');
        }

        Gate::forUser($actor)->authorize('create', Department::class);

        $companyId = $this->tenantContext->companyId();

        if ($companyId === null) {
            throw new AuthorizationException('A current company is required to create departments.');
        }

        return DB::transaction(function () use ($actor, $companyId, $data): Department {
            $department = Department::create([
                ...$data,
                'company_id' => $companyId,
            ]);

            $this->auditLogger->log(
                action: 'department.created',
                auditable: $department,
                newValues: $department->attributesToArray(),
                user: $actor,
                company: $companyId,
            );

            return $department;
        });
    }
}
