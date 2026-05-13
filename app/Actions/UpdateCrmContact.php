<?php

namespace App\Actions;

use App\Models\CrmContact;
use App\Models\CrmLead;
use App\Models\Customer;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateCrmContact
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(CrmContact $contact, array $data, ?User $actor = null): CrmContact
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($contact, $actor);
        $this->authorize($actor, 'crm_contacts.update', $contact->company_id);
        $this->ensureRelationsBelongToCompany($data, $contact->company_id);
        unset($data['company_id']);

        return DB::transaction(function () use ($contact, $actor, $data): CrmContact {
            $oldValues = $contact->attributesToArray();

            $contact->update($data);

            $this->auditLogger->log('crm_contact.updated', $contact, $oldValues, $contact->refresh()->attributesToArray(), user: $actor, company: $contact->company_id);

            return $contact;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function ensureRelationsBelongToCompany(array $data, int $companyId): void
    {
        if (array_key_exists('customer_id', $data) && $data['customer_id'] !== null && ! Customer::query()->whereKey($data['customer_id'])->where('company_id', $companyId)->exists()) {
            throw ValidationException::withMessages([
                'customer_id' => __('crm.validation.contacts.customer_company'),
            ]);
        }

        if (array_key_exists('lead_id', $data) && $data['lead_id'] !== null && ! CrmLead::query()->whereKey($data['lead_id'])->where('company_id', $companyId)->exists()) {
            throw ValidationException::withMessages([
                'lead_id' => __('crm.validation.contacts.lead_company'),
            ]);
        }
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to update CRM contacts.');
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
