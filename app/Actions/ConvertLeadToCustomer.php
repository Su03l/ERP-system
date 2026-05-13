<?php

namespace App\Actions;

use App\Models\CrmLead;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use LogicException;

class ConvertLeadToCustomer
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    public function handle(CrmLead $lead, ?User $actor = null): never
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($lead, $actor);
        $this->authorize($actor, 'crm_leads.convert', $lead->company_id);

        $this->auditLogger->log('crm_lead.conversion_attempted', $lead, metadata: [
            'reason' => 'customer_conversion_placeholder',
        ], user: $actor, company: $lead->company_id);

        throw new LogicException('CRM lead conversion to customer is not implemented yet.');
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to convert CRM leads.');
        }

        return $actor;
    }

    private function ensureTenant(CrmLead $lead, User $actor): void
    {
        if ($this->tenantContext->companyId() !== $lead->company_id || $actor->company_id !== $lead->company_id) {
            throw new AuthorizationException('CRM lead does not belong to the current company.');
        }
    }

    private function authorize(User $actor, string $permission, int $companyId): void
    {
        if (! $actor->hasPermission($permission, $companyId)) {
            throw new AuthorizationException('This action is unauthorized.');
        }
    }
}
