<?php

namespace App\Policies;

use App\Models\CompanySubscription;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class CompanySubscriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return Gate::forUser($user)->allows('subscriptions.view');
    }

    public function view(User $user, CompanySubscription $companySubscription): bool
    {
        return Gate::forUser($user)->allows('subscriptions.view');
    }

    public function create(User $user): bool
    {
        return Gate::forUser($user)->allows('subscriptions.create');
    }

    public function update(User $user, CompanySubscription $companySubscription): bool
    {
        return Gate::forUser($user)->allows('subscriptions.update');
    }

    public function cancel(User $user, CompanySubscription $companySubscription): bool
    {
        return Gate::forUser($user)->allows('subscriptions.cancel');
    }
}
