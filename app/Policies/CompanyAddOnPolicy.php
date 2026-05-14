<?php

namespace App\Policies;

use App\Models\CompanyAddOn;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class CompanyAddOnPolicy
{
    public function viewAny(User $user): bool
    {
        return Gate::forUser($user)->allows('company_add_ons.manage');
    }

    public function view(User $user, CompanyAddOn $companyAddOn): bool
    {
        return Gate::forUser($user)->allows('company_add_ons.manage');
    }

    public function create(User $user): bool
    {
        return Gate::forUser($user)->allows('company_add_ons.manage');
    }

    public function update(User $user, CompanyAddOn $companyAddOn): bool
    {
        return Gate::forUser($user)->allows('company_add_ons.manage');
    }
}
