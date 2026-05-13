<?php

namespace App\Actions;

use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTimeLog;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\CalculateLoggedMinutes;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateTimeLog
{
    public function __construct(private readonly AuditLogger $auditLogger, private readonly TenantContext $tenantContext, private readonly CalculateLoggedMinutes $calculateLoggedMinutes) {}

    /** @param array<string, mixed> $data */
    public function handle(ProjectTimeLog $timeLog, array $data, ?User $actor = null): ProjectTimeLog
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($timeLog, $actor);
        $this->authorize($actor, 'project_time_logs.update', $timeLog->company_id);
        $this->ensureRelations($timeLog, $data);
        $data = $this->withCalculatedMinutes($timeLog, $data);
        unset($data['company_id']);

        return DB::transaction(function () use ($timeLog, $actor, $data): ProjectTimeLog {
            $oldValues = $timeLog->attributesToArray();
            $timeLog->update($data);
            $this->auditLogger->log('project_time_log.updated', $timeLog, $oldValues, $timeLog->refresh()->attributesToArray(), user: $actor, company: $timeLog->company_id);

            return $timeLog;
        });
    }

    /** @param array<string, mixed> $data */
    private function withCalculatedMinutes(ProjectTimeLog $timeLog, array $data): array
    {
        $startTime = $data['start_time'] ?? $timeLog->start_time;
        $endTime = $data['end_time'] ?? $timeLog->end_time;

        if ($startTime !== null && $endTime !== null) {
            $data['total_minutes'] = $this->calculateLoggedMinutes->handle((string) $startTime, (string) $endTime);
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    private function ensureRelations(ProjectTimeLog $timeLog, array $data): void
    {
        $companyId = $timeLog->company_id;
        $projectId = $data['project_id'] ?? $timeLog->project_id;
        if (! Project::query()->whereKey($projectId)->where('company_id', $companyId)->exists()) {
            throw ValidationException::withMessages(['project_id' => __('validation.exists', ['attribute' => 'project_id'])]);
        }
        if (array_key_exists('employee_id', $data) && ! Employee::query()->whereKey($data['employee_id'])->where('company_id', $companyId)->exists()) {
            throw ValidationException::withMessages(['employee_id' => __('validation.exists', ['attribute' => 'employee_id'])]);
        }
        if (array_key_exists('project_task_id', $data) && $data['project_task_id'] !== null && ! ProjectTask::query()->whereKey($data['project_task_id'])->where('company_id', $companyId)->where('project_id', $projectId)->exists()) {
            throw ValidationException::withMessages(['project_task_id' => __('validation.exists', ['attribute' => 'project_task_id'])]);
        }
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();
        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to update time logs.');
        }

        return $actor;
    }

    private function ensureTenant(ProjectTimeLog $timeLog, User $actor): void
    {
        if ($this->tenantContext->companyId() !== $timeLog->company_id || $actor->company_id !== $timeLog->company_id) {
            throw new AuthorizationException('Project time log does not belong to the current company.');
        }
    }

    private function authorize(User $actor, string $permission, int $companyId): void
    {
        if (! $actor->hasPermission($permission, $companyId)) {
            throw new AuthorizationException('This action is unauthorized.');
        }
    }
}
