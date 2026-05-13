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

class CreateProject
{
    public function __construct(private readonly AuditLogger $auditLogger, private readonly TenantContext $tenantContext) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data, ?User $actor = null): Project
    {
        $actor = $this->actor($actor);
        $companyId = $this->companyId($actor);
        $this->authorize($actor, 'projects.create', $companyId);
        $this->ensureRelations($data, $companyId);
        unset($data['company_id']);

        return DB::transaction(function () use ($actor, $companyId, $data): Project {
            $project = Project::create([...$data, 'company_id' => $companyId]);
            $this->auditLogger->log('project.created', $project, newValues: $project->attributesToArray(), user: $actor, company: $companyId);

            return $project;
        });
    }

    /** @param array<string, mixed> $data */
    private function ensureRelations(array $data, int $companyId): void
    {
        if (($data['customer_id'] ?? null) !== null && ! Customer::query()->whereKey($data['customer_id'])->where('company_id', $companyId)->exists()) {
            throw ValidationException::withMessages(['customer_id' => __('crm.validation.contacts.customer_company')]);
        }

        if (($data['project_manager_id'] ?? null) !== null && ! Employee::query()->whereKey($data['project_manager_id'])->where('company_id', $companyId)->exists()) {
            throw ValidationException::withMessages(['project_manager_id' => __('hr.validation.employees.company')]);
        }
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to create projects.');
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
