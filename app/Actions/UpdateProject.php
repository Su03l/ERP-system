<?php

namespace App\Actions;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Project;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateProject
{
    public function __construct(private readonly AuditLogger $auditLogger, private readonly TenantContext $tenantContext) {}

    /** @param array<string, mixed> $data */
    public function handle(Project $project, array $data, ?User $actor = null): Project
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($project, $actor);
        $this->authorize($actor, 'projects.update', $project->company_id);
        $this->ensureRelations($data, $project->company_id);
        unset($data['company_id']);

        return DB::transaction(function () use ($project, $actor, $data): Project {
            $oldValues = $project->attributesToArray();
            $project->update($data);
            $this->auditLogger->log('project.updated', $project, $oldValues, $project->refresh()->attributesToArray(), user: $actor, company: $project->company_id);

            return $project;
        });
    }

    /** @param array<string, mixed> $data */
    private function ensureRelations(array $data, int $companyId): void
    {
        if (array_key_exists('customer_id', $data) && $data['customer_id'] !== null && ! Customer::query()->whereKey($data['customer_id'])->where('company_id', $companyId)->exists()) {
            throw ValidationException::withMessages(['customer_id' => __('crm.validation.contacts.customer_company')]);
        }

        if (array_key_exists('project_manager_id', $data) && $data['project_manager_id'] !== null && ! Employee::query()->whereKey($data['project_manager_id'])->where('company_id', $companyId)->exists()) {
            throw ValidationException::withMessages(['project_manager_id' => __('hr.validation.employees.company')]);
        }
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to update projects.');
        }

        return $actor;
    }

    private function ensureTenant(Project $project, User $actor): void
    {
        if ($this->tenantContext->companyId() !== $project->company_id || $actor->company_id !== $project->company_id) {
            throw new AuthorizationException('Project does not belong to the current company.');
        }
    }

    private function authorize(User $actor, string $permission, int $companyId): void
    {
        if (! $actor->hasPermission($permission, $companyId)) {
            throw new AuthorizationException('This action is unauthorized.');
        }
    }
}
