<?php

namespace App\Actions;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CreateEmployeeDocument
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
    public function handle(array $data, ?User $actor = null): EmployeeDocument
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to create employee documents.');
        }

        Gate::forUser($actor)->authorize('create', EmployeeDocument::class);

        $companyId = $this->tenantContext->companyId();

        if ($companyId === null) {
            throw new AuthorizationException('A current company is required to create employee documents.');
        }

        $this->ensureEmployeeBelongsToCompany((int) $data['employee_id'], $companyId);

        return DB::transaction(function () use ($actor, $companyId, $data): EmployeeDocument {
            $employeeDocument = EmployeeDocument::create([
                ...$data,
                'company_id' => $companyId,
            ]);

            $this->auditLogger->log(
                action: 'employee_document.created',
                auditable: $employeeDocument,
                newValues: $employeeDocument->attributesToArray(),
                user: $actor,
                company: $companyId,
            );

            return $employeeDocument;
        });
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
}
