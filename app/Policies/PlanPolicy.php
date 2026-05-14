<?php

namespace App\Policies;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class PlanPolicy
{
    public function viewAny(User $user): bool
    {
        return Gate::forUser($user)->allows('plans.view');
    }

    public function view(User $user, Plan $plan): bool
    {
        return Gate::forUser($user)->allows('plans.view');
    }

    public function create(User $user): bool
    {
        return Gate::forUser($user)->allows('plans.create');
    }

    public function update(User $user, Plan $plan): bool
    {
        return Gate::forUser($user)->allows('plans.update');
    }

    public function delete(User $user, Plan $plan): bool
    {
        return Gate::forUser($user)->allows('plans.delete');
    }
}
