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

class CreateManualTimeLog
{
    public function __construct(private readonly AuditLogger $auditLogger, private readonly TenantContext $tenantContext, private readonly CalculateLoggedMinutes $calculateLoggedMinutes) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data, ?User $actor = null): ProjectTimeLog
    {
        $actor = $this->actor($actor);
        $companyId = $this->companyId($actor);
        $this->authorize($actor, 'project_time_logs.create', $companyId);
        $this->ensureRelations($data, $companyId);
        $data = $this->withCalculatedMinutes($data);
        unset($data['company_id']);

        return DB::transaction(function () use ($actor, $companyId, $data): ProjectTimeLog {
            $timeLog = ProjectTimeLog::create([...$data, 'company_id' => $companyId]);
            $this->auditLogger->log('project_time_log.created', $timeLog, newValues: $timeLog->attributesToArray(), user: $actor, company: $companyId);

            return $timeLog;
        });
    }

    /** @param array<string, mixed> $data */
    private function withCalculatedMinutes(array $data): array
    {
        if (($data['start_time'] ?? null) !== null && ($data['end_time'] ?? null) !== null) {
            $data['total_minutes'] = $this->calculateLoggedMinutes->handle((string) $data['start_time'], (string) $data['end_time']);
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    private function ensureRelations(array $data, int $companyId): void
    {
        $project = Project::query()->whereKey($data['project_id'] ?? null)->where('company_id', $companyId)->first();
        if ($project === null) {
            throw ValidationException::withMessages(['project_id' => __('validation.exists', ['attribute' => 'project_id'])]);
        }
        if (! Employee::query()->whereKey($data['employee_id'] ?? null)->where('company_id', $companyId)->exists()) {
            throw ValidationException::withMessages(['employee_id' => __('validation.exists', ['attribute' => 'employee_id'])]);
        }
        if (($data['project_task_id'] ?? null) !== null && ! ProjectTask::query()->whereKey($data['project_task_id'])->where('company_id', $companyId)->where('project_id', $project->id)->exists()) {
            throw ValidationException::withMessages(['project_task_id' => __('validation.exists', ['attribute' => 'project_task_id'])]);
        }
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();
        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to create time logs.');
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
