<?php

namespace App\Actions;

use App\Enums\ProjectTaskStatus;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use App\Models\Workflow;
use App\Services\AuditLogger;
use App\Services\WorkflowExecutionService;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateProjectTask
{
    public function __construct(private readonly AuditLogger $auditLogger, private readonly TenantContext $tenantContext, private readonly RecalculateProjectProgress $recalculateProjectProgress, private readonly WorkflowExecutionService $workflowExecutionService) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data, ?User $actor = null): ProjectTask
    {
        $actor = $this->actor($actor);
        $companyId = $this->companyId($actor);
        $this->authorize($actor, 'project_tasks.create', $companyId);
        $this->ensureRelations($data, $companyId);
        unset($data['company_id']);

        return DB::transaction(function () use ($actor, $companyId, $data): ProjectTask {
            $task = ProjectTask::create([...$data, 'company_id' => $companyId]);
            $task->loadMissing('project.company.projectCrmSetting');

            if (($task->project->company->projectCrmSetting?->task_approval_required ?? false) && $workflow = $this->workflow($companyId, 'task_approval')) {
                $instance = $this->workflowExecutionService->start($workflow, $actor, $task, [
                    'project_id' => $task->project_id,
                    'project_task_id' => $task->id,
                ]);
                $task->forceFill([
                    'workflow_instance_id' => $instance->id,
                    'status' => ProjectTaskStatus::PendingApproval,
                ])->save();
            }

            $this->auditLogger->log('project_task.created', $task, newValues: $task->attributesToArray(), user: $actor, company: $companyId);
            $this->recalculateProjectProgress->handle($task->project, $actor);

            return $task;
        });
    }

    private function workflow(int $companyId, string $triggerType): ?Workflow
    {
        return Workflow::query()
            ->where('company_id', $companyId)
            ->where('module_key', 'projects')
            ->where('trigger_type', $triggerType)
            ->where('status', 'active')
            ->with('steps')
            ->latest('id')
            ->first();
    }

    /** @param array<string, mixed> $data */
    private function ensureRelations(array $data, int $companyId): void
    {
        $project = Project::query()->whereKey($data['project_id'] ?? null)->where('company_id', $companyId)->first();
        if ($project === null) {
            throw ValidationException::withMessages(['project_id' => __('validation.exists', ['attribute' => 'project_id'])]);
        }

        if (($data['assigned_employee_id'] ?? null) !== null && ! Employee::query()->whereKey($data['assigned_employee_id'])->where('company_id', $companyId)->exists()) {
            throw ValidationException::withMessages(['assigned_employee_id' => __('validation.exists', ['attribute' => 'assigned_employee_id'])]);
        }

        if (($data['parent_task_id'] ?? null) !== null && ! ProjectTask::query()->whereKey($data['parent_task_id'])->where('company_id', $companyId)->where('project_id', $project->id)->exists()) {
            throw ValidationException::withMessages(['parent_task_id' => __('validation.exists', ['attribute' => 'parent_task_id'])]);
        }
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();
        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to create project tasks.');
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
