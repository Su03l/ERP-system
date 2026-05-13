<?php

namespace App\Actions;

use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateProjectTask
{
    public function __construct(private readonly AuditLogger $auditLogger, private readonly TenantContext $tenantContext, private readonly RecalculateProjectProgress $recalculateProjectProgress) {}

    /** @param array<string, mixed> $data */
    public function handle(ProjectTask $task, array $data, ?User $actor = null): ProjectTask
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($task, $actor);
        $this->authorize($actor, 'project_tasks.update', $task->company_id);
        $this->ensureRelations($task, $data);
        unset($data['company_id']);

        return DB::transaction(function () use ($task, $actor, $data): ProjectTask {
            $oldProject = $task->project;
            $oldValues = $task->attributesToArray();
            $task->update($data);
            $this->auditLogger->log('project_task.updated', $task, $oldValues, $task->refresh()->attributesToArray(), user: $actor, company: $task->company_id);
            $this->recalculateProjectProgress->handle($task->project, $actor);
            if (! $oldProject->is($task->project)) {
                $this->recalculateProjectProgress->handle($oldProject, $actor);
            }

            return $task;
        });
    }

    /** @param array<string, mixed> $data */
    private function ensureRelations(ProjectTask $task, array $data): void
    {
        $companyId = $task->company_id;
        $projectId = $data['project_id'] ?? $task->project_id;

        if (! Project::query()->whereKey($projectId)->where('company_id', $companyId)->exists()) {
            throw ValidationException::withMessages(['project_id' => __('validation.exists', ['attribute' => 'project_id'])]);
        }

        if (array_key_exists('assigned_employee_id', $data) && $data['assigned_employee_id'] !== null && ! Employee::query()->whereKey($data['assigned_employee_id'])->where('company_id', $companyId)->exists()) {
            throw ValidationException::withMessages(['assigned_employee_id' => __('validation.exists', ['attribute' => 'assigned_employee_id'])]);
        }

        if (array_key_exists('parent_task_id', $data) && $data['parent_task_id'] !== null) {
            if ((int) $data['parent_task_id'] === $task->id || ! ProjectTask::query()->whereKey($data['parent_task_id'])->where('company_id', $companyId)->where('project_id', $projectId)->exists()) {
                throw ValidationException::withMessages(['parent_task_id' => __('validation.exists', ['attribute' => 'parent_task_id'])]);
            }
        }
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();
        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to update project tasks.');
        }

        return $actor;
    }

    private function ensureTenant(ProjectTask $task, User $actor): void
    {
        if ($this->tenantContext->companyId() !== $task->company_id || $actor->company_id !== $task->company_id) {
            throw new AuthorizationException('Project task does not belong to the current company.');
        }
    }

    private function authorize(User $actor, string $permission, int $companyId): void
    {
        if (! $actor->hasPermission($permission, $companyId)) {
            throw new AuthorizationException('This action is unauthorized.');
        }
    }
}
