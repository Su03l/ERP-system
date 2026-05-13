<?php

namespace App\Policies;

use App\Models\ProjectTimeLog;
use App\Models\User;

class ProjectTimeLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'project_time_logs.view');
    }

    public function view(User $user, ProjectTimeLog $projectTimeLog): bool
    {
        return $this->sameCompany($user, $projectTimeLog->company_id) && $user->hasPermission('project_time_logs.view', $projectTimeLog->company_id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'project_time_logs.create');
    }

    public function update(User $user, ProjectTimeLog $projectTimeLog): bool
    {
        return $this->sameCompany($user, $projectTimeLog->company_id) && $user->hasPermission('project_time_logs.update', $projectTimeLog->company_id);
    }

    public function delete(User $user, ProjectTimeLog $projectTimeLog): bool
    {
        return $this->sameCompany($user, $projectTimeLog->company_id) && $user->hasPermission('project_time_logs.delete', $projectTimeLog->company_id);
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
