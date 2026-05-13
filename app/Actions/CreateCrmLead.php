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

class CreateCrmLead
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?User $actor = null): CrmLead
    {
        $actor = $this->actor($actor);
        $companyId = $this->companyId($actor);
        $this->authorize($actor, 'crm_leads.create', $companyId);
        $this->ensureAssignedUserBelongsToCompany($data['assigned_user_id'] ?? null, $companyId);
        unset($data['company_id']);

        return DB::transaction(function () use ($actor, $companyId, $data): CrmLead {
            $lead = CrmLead::create([
                ...$data,
                'company_id' => $companyId,
            ]);

            $this->auditLogger->log('crm_lead.created', $lead, newValues: $lead->attributesToArray(), user: $actor, company: $companyId);

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
            throw new AuthorizationException('An authenticated user is required to create CRM leads.');
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
