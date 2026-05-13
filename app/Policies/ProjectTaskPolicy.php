<?php

namespace App\Policies;

use App\Models\ProjectTask;
use App\Models\User;

class ProjectTaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'project_tasks.view');
    }

    public function view(User $user, ProjectTask $projectTask): bool
    {
        return $this->sameCompany($user, $projectTask->company_id) && $user->hasPermission('project_tasks.view', $projectTask->company_id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'project_tasks.create');
    }

    public function update(User $user, ProjectTask $projectTask): bool
    {
        return $this->sameCompany($user, $projectTask->company_id) && $user->hasPermission('project_tasks.update', $projectTask->company_id);
    }

    public function complete(User $user, ProjectTask $projectTask): bool
    {
        return $this->sameCompany($user, $projectTask->company_id) && $user->hasPermission('project_tasks.complete', $projectTask->company_id);
    }

    public function delete(User $user, ProjectTask $projectTask): bool
    {
        return $this->sameCompany($user, $projectTask->company_id) && $user->hasPermission('project_tasks.delete', $projectTask->company_id);
    }

    private function can(User $user, string $permission): bool
    {
        return $user->company_id !== null && $user->hasPermission($permission, $user->company_id);
    }

    private function sameCompany(User $user, int $companyId): bool
    {
        return $user->company_id !== null && $user->company_id === $companyId;
    }
}
