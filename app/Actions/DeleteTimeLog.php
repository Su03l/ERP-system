<?php

namespace App\Actions;

use App\Models\ProjectTimeLog;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeleteTimeLog
{
    public function __construct(private readonly AuditLogger $auditLogger, private readonly TenantContext $tenantContext) {}

    public function handle(ProjectTimeLog $timeLog, ?User $actor = null): void
    {
        $actor = $this->actor($actor);
        $this->ensureTenant($timeLog, $actor);
        $this->authorize($actor, 'project_time_logs.delete', $timeLog->company_id);

        DB::transaction(function () use ($timeLog, $actor): void {
            $oldValues = $timeLog->attributesToArray();
            $this->auditLogger->log('project_time_log.deleted', $timeLog, $oldValues, user: $actor, company: $timeLog->company_id);
            $timeLog->delete();
        });
    }

    private function actor(?User $actor): User
    {
        $actor ??= Auth::user();
        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to delete time logs.');
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
