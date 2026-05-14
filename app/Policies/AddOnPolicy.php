<?php

namespace App\Policies;

use App\Models\AddOn;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class AddOnPolicy
{
    public function viewAny(User $user): bool
    {
        return Gate::forUser($user)->allows('add_ons.view');
    }

    public function view(User $user, AddOn $addOn): bool
    {
        return Gate::forUser($user)->allows('add_ons.view');
    }

    public function create(User $user): bool
    {
        return Gate::forUser($user)->allows('add_ons.create');
    }

    public function update(User $user, AddOn $addOn): bool
    {
        return Gate::forUser($user)->allows('add_ons.update');
    }

    public function delete(User $user, AddOn $addOn): bool
    {
        return Gate::forUser($user)->allows('add_ons.delete');
    }
}
