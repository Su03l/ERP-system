<?php

namespace App\Policies;

use App\Models\JobTitle;
use App\Models\User;

class JobTitlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null && $user->hasPermission('job_titles.view', $user->company_id);
    }

    public function view(User $user, JobTitle $jobTitle): bool
    {
        return $this->belongsToUsersCompany($user, $jobTitle) && $user->hasPermission('job_titles.view', $jobTitle->company_id);
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null && $user->hasPermission('job_titles.create', $user->company_id);
    }

    public function update(User $user, JobTitle $jobTitle): bool
    {
        return $this->belongsToUsersCompany($user, $jobTitle) && $user->hasPermission('job_titles.update', $jobTitle->company_id);
    }

    public function delete(User $user, JobTitle $jobTitle): bool
    {
        return $this->belongsToUsersCompany($user, $jobTitle) && $user->hasPermission('job_titles.delete', $jobTitle->company_id);
    }

    public function restore(User $user, JobTitle $jobTitle): bool
    {
        return $this->delete($user, $jobTitle);
    }

    public function forceDelete(User $user, JobTitle $jobTitle): bool
    {
        return false;
    }

    private function belongsToUsersCompany(User $user, JobTitle $jobTitle): bool
    {
        return $user->company_id !== null && $user->company_id === $jobTitle->company_id;
    }
}
