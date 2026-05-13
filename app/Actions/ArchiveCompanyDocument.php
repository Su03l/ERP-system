<?php

namespace App\Actions;

use App\Enums\DocumentStatus;
use App\Models\CompanyDocument;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ArchiveCompanyDocument
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    public function handle(CompanyDocument $document, ?User $actor = null): CompanyDocument
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($document, $actor);
        Gate::forUser($actor)->authorize('delete', $document);

        return DB::transaction(function () use ($document, $actor): CompanyDocument {
            $oldValues = $document->attributesToArray();
            $document->forceFill(['status' => DocumentStatus::Archived])->save();

            $this->auditLogger->log('company_document.archived', $document, $oldValues, $document->refresh()->attributesToArray(), user: $actor, company: $document->company_id);

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
            throw new AuthorizationException('An authenticated user is required to archive company documents.');
        }

        return $actor;
    }
}
