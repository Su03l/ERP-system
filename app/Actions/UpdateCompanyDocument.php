<?php

namespace App\Actions;

use App\Models\CompanyDocument;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UpdateCompanyDocument
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(CompanyDocument $document, array $data, ?User $actor = null): CompanyDocument
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($document, $actor);
        Gate::forUser($actor)->authorize('update', $document);
        unset($data['company_id']);

        return DB::transaction(function () use ($document, $actor, $data): CompanyDocument {
            $oldValues = $document->attributesToArray();
            $document->update($data);

            $this->auditLogger->log('company_document.updated', $document, $oldValues, $document->refresh()->attributesToArray(), user: $actor, company: $document->company_id);

            return $document;
        });
    }

    private function ensureTenant(CompanyDocument $document, User $actor): void
    {
        if ($this->tenantContext->companyId() !== $document->company_id || $actor->company_id !== $document->company_id) {
            throw new AuthorizationException('Company document does not belong to the current company.');
        }
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to update company documents.');
        }

        return $actor;
    }
}
