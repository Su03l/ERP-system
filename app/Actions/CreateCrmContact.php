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

class CreateCrmContact
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?User $actor = null): CrmContact
    {
        $actor = $this->actor($actor);
        $companyId = $this->companyId($actor);
        $this->authorize($actor, 'crm_contacts.create', $companyId);
        $this->ensureRelationsBelongToCompany($data, $companyId);
        unset($data['company_id']);

        return DB::transaction(function () use ($actor, $companyId, $data): CrmContact {
            $contact = CrmContact::create([
                ...$data,
                'company_id' => $companyId,
            ]);

            $this->auditLogger->log('crm_contact.created', $contact, newValues: $contact->attributesToArray(), user: $actor, company: $companyId);

            return $contact;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function ensureRelationsBelongToCompany(array $data, int $companyId): void
    {
        if (($data['customer_id'] ?? null) !== null && ! Customer::query()->whereKey($data['customer_id'])->where('company_id', $companyId)->exists()) {
            throw ValidationException::withMessages([
                'customer_id' => __('crm.validation.contacts.customer_company'),
            ]);
        }

        if (($data['lead_id'] ?? null) !== null && ! CrmLead::query()->whereKey($data['lead_id'])->where('company_id', $companyId)->exists()) {
            throw ValidationException::withMessages([
                'lead_id' => __('crm.validation.contacts.lead_company'),
            ]);
        }
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to create CRM contacts.');
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

    private function authorize(User $actor, string $permission, int $companyId): void
    {
        if (! $actor->hasPermission($permission, $companyId)) {
            throw new AuthorizationException('This action is unauthorized.');
        }
    }
}
