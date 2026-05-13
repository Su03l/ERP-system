<?php

namespace App\Actions;

use App\Models\CrmContact;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ArchiveCrmContact
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    public function handle(CrmContact $contact, ?User $actor = null): CrmContact
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($contact, $actor);
        $this->authorize($actor, 'crm_contacts.delete', $contact->company_id);

        return DB::transaction(function () use ($contact, $actor): CrmContact {
            $oldValues = $contact->attributesToArray();

            $contact->delete();

            $this->auditLogger->log('crm_contact.archived', $contact, $oldValues, $contact->attributesToArray(), user: $actor, company: $contact->company_id);

            return $contact;
        });
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to archive CRM contacts.');
        }

        return $actor;
    }

    private function ensureTenant(CrmContact $contact, User $actor): void
    {
        if ($this->tenantContext->companyId() !== $contact->company_id || $actor->company_id !== $contact->company_id) {
            throw new AuthorizationException('CRM contact does not belong to the current company.');
        }
    }

    private function authorize(User $actor, string $permission, int $companyId): void
    {
        if (! $actor->hasPermission($permission, $companyId)) {
            throw new AuthorizationException('This action is unauthorized.');
        }
    }
}
