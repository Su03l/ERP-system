<?php

namespace App\Actions;

use App\Models\CrmLead;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateCrmLead
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(CrmLead $lead, array $data, ?User $actor = null): CrmLead
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($lead, $actor);
        $this->authorize($actor, 'crm_leads.update', $lead->company_id);
        $this->ensureAssignedUserBelongsToCompany($data['assigned_user_id'] ?? null, $lead->company_id);
        unset($data['company_id']);

        return DB::transaction(function () use ($lead, $actor, $data): CrmLead {
            $oldValues = $lead->attributesToArray();

            $lead->update($data);

            $this->auditLogger->log('crm_lead.updated', $lead, $oldValues, $lead->refresh()->attributesToArray(), user: $actor, company: $lead->company_id);

            return $lead;
        });
    }

    private function ensureAssignedUserBelongsToCompany(mixed $assignedUserId, int $companyId): void
    {
        if ($assignedUserId === null) {
            return;
        }

        if (! User::query()->whereKey($assignedUserId)->where('company_id', $companyId)->exists()) {
            throw ValidationException::withMessages([
                'assigned_user_id' => __('crm.validation.leads.assigned_user_company'),
            ]);
        }
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to update CRM leads.');
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
