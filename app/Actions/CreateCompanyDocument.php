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

class CreateCompanyDocument
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?User $actor = null): CompanyDocument
    {
        $actor = $this->actor($actor);
        Gate::forUser($actor)->authorize('create', CompanyDocument::class);
        $companyId = $this->companyId($actor);
        unset($data['company_id']);

        return DB::transaction(function () use ($actor, $companyId, $data): CompanyDocument {
            $document = CompanyDocument::create([...$data, 'company_id' => $companyId]);

            $this->auditLogger->log('company_document.created', $document, newValues: $document->attributesToArray(), user: $actor, company: $companyId);

            return $document;
        });
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to create company documents.');
        }

        return $actor;
    }

    private function companyId(User $actor): int
    {
        $companyId = $this->tenantContext->companyId();

        if ($companyId === null || $actor->company_id !== $companyId) {
            throw new AuthorizationException('A current company is required.');
        }

        return $companyId;
    }
}
