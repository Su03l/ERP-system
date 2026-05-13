<?php

namespace App\Actions;

use App\Enums\ProjectTaskStatus;
use App\Models\ProjectTask;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompleteProjectTask
{
    public function __construct(private readonly AuditLogger $auditLogger, private readonly TenantContext $tenantContext, private readonly RecalculateProjectProgress $recalculateProjectProgress) {}

    public function handle(ProjectTask $task, ?User $actor = null): ProjectTask
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($task, $actor);
        $this->authorize($actor, 'project_tasks.complete', $task->company_id);

        return DB::transaction(function () use ($task, $actor): ProjectTask {
            $oldValues = $task->attributesToArray();
            $task->forceFill([
                'status' => ProjectTaskStatus::Completed,
                'progress_percentage' => 100,
                'completed_at' => $task->completed_at ?? now(),
            ])->save();
            $this->auditLogger->log('project_task.completed', $task, $oldValues, $task->refresh()->attributesToArray(), user: $actor, company: $task->company_id);
            $this->recalculateProjectProgress->handle($task->project, $actor);

            return $task;
        });
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();
        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to complete project tasks.');
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
