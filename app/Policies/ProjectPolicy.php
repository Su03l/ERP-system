<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->can($user, 'projects.view');
    }

    public function view(User $user, Project $project): bool
    {
        return $this->sameCompany($user, $project->company_id) && $user->hasPermission('projects.view', $project->company_id);
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'projects.create');
    }

    public function update(User $user, Project $project): bool
    {
        return $this->sameCompany($user, $project->company_id) && $user->hasPermission('projects.update', $project->company_id);
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->sameCompany($user, $project->company_id) && $user->hasPermission('projects.delete', $project->company_id);
    }

    public function export(User $user): bool
    {
        return $this->can($user, 'projects.export');
    }

    public function restore(User $user, Project $project): bool
    {
        return $this->delete($user, $project);
    }

    public function forceDelete(User $user, Project $project): bool
    {
        return false;
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
