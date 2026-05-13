<?php

namespace App\Actions;

use App\Models\Project;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ArchiveProject
{
    public function __construct(private readonly AuditLogger $auditLogger, private readonly TenantContext $tenantContext) {}

    public function handle(Project $project, ?User $actor = null): Project
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($project, $actor);
        $this->authorize($actor, 'projects.delete', $project->company_id);

        return DB::transaction(function () use ($project, $actor): Project {
            $oldValues = $project->attributesToArray();
            $project->delete();
            $this->auditLogger->log('project.archived', $project, $oldValues, $project->attributesToArray(), user: $actor, company: $project->company_id);

            return $project;
        });
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to archive projects.');
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
