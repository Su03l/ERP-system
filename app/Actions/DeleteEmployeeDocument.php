<?php

namespace App\Actions;

use App\Models\EmployeeDocument;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DeleteEmployeeDocument
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @throws AuthorizationException
     */
    public function handle(EmployeeDocument $employeeDocument, ?User $actor = null): void
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to delete employee documents.');
        }

        Gate::forUser($actor)->authorize('delete', $employeeDocument);
        $this->ensureDocumentBelongsToCurrentCompany($employeeDocument);

        DB::transaction(function () use ($actor, $employeeDocument): void {
            $oldValues = $employeeDocument->attributesToArray();

            $employeeDocument->delete();

            $this->auditLogger->log(
                action: 'employee_document.deleted',
                auditable: $employeeDocument,
                oldValues: $oldValues,
                user: $actor,
                company: $employeeDocument->company_id,
            );
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
}
