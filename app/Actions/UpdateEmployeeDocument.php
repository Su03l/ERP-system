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

class UpdateEmployeeDocument
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
    public function handle(EmployeeDocument $employeeDocument, array $data, ?User $actor = null): EmployeeDocument
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to update employee documents.');
        }

        Gate::forUser($actor)->authorize('update', $employeeDocument);
        $this->ensureDocumentBelongsToCurrentCompany($employeeDocument);

        if (array_key_exists('employee_id', $data)) {
            $this->ensureEmployeeBelongsToCompany((int) $data['employee_id'], $employeeDocument->company_id);
        }

        return DB::transaction(function () use ($actor, $data, $employeeDocument): EmployeeDocument {
            $oldValues = $employeeDocument->attributesToArray();

            $employeeDocument->fill($data);
            $employeeDocument->save();

            $this->auditLogger->log(
                action: 'employee_document.updated',
                auditable: $employeeDocument,
                oldValues: $oldValues,
                newValues: $employeeDocument->refresh()->attributesToArray(),
                user: $actor,
                company: $employeeDocument->company_id,
            );

            return $employeeDocument;
        });
    }

    /**
     * @throws AuthorizationException
     */
    private function ensureDocumentBelongsToCurrentCompany(EmployeeDocument $employeeDocument): void
    {
        if ($this->tenantContext->companyId() !== $employeeDocument->company_id) {
            throw new AuthorizationException('Employee document does not belong to the current company.');
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
}
