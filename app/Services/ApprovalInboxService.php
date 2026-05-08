<?php

namespace App\Services;

use App\Models\User;
use App\Models\WorkflowInstance;
use App\Models\WorkflowStep;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ApprovalInboxService
{
    public function __construct(private readonly TenantContext $tenantContext) {}

    /**
     * @param  array{module_key?: string, status?: string}  $filters
     * @return Collection<int, WorkflowInstance>
     */
    public function pendingFor(User $user, array $filters = []): Collection
    {
        $companyId = $this->tenantContext->companyId();

        if ($companyId === null || $user->company_id !== $companyId) {
            return new Collection;
        }

        return WorkflowInstance::query()
            ->with(['workflow', 'currentStep', 'requestedBy'])
            ->where('company_id', $companyId)
            ->where('status', $filters['status'] ?? 'pending')
            ->when(
                isset($filters['module_key']),
                fn (Builder $query): Builder => $query->whereHas(
                    'workflow',
                    fn (Builder $workflowQuery): Builder => $workflowQuery->where('module_key', $filters['module_key']),
                ),
            )
            ->whereHas('currentStep', fn (Builder $query): Builder => $this->whereAssignedToUser($query, $user, $companyId))
            ->latest()
            ->get();
    }

    /**
     * @param  Builder<WorkflowStep>  $query
     * @return Builder<WorkflowStep>
     */
    private function whereAssignedToUser(Builder $query, User $user, int $companyId): Builder
    {
        $roleIds = $user->roles()
            ->wherePivot('company_id', $companyId)
            ->pluck('roles.id')
            ->map(fn (int $roleId): string => (string) $roleId)
            ->all();

        $permissionKeys = $user->roles()
            ->wherePivot('company_id', $companyId)
            ->with('permissions:id,key')
            ->get()
            ->flatMap(fn ($role) => $role->permissions->pluck('key'))
            ->unique()
            ->values()
            ->all();

        return $query->where(function (Builder $stepQuery) use ($user, $roleIds, $permissionKeys): void {
            $stepQuery
                ->where(fn (Builder $userQuery): Builder => $userQuery
                    ->where('approver_type', 'user')
                    ->where('approver_value', (string) $user->id))
                ->orWhere(fn (Builder $roleQuery): Builder => $roleQuery
                    ->where('approver_type', 'role')
                    ->whereIn('approver_value', $roleIds))
                ->orWhere(fn (Builder $permissionQuery): Builder => $permissionQuery
                    ->where('approver_type', 'permission')
                    ->whereIn('approver_value', $permissionKeys));
        });
    }
}
