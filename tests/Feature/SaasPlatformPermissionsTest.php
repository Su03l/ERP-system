<?php

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

function grantTenantPermission(User $user, string $permissionKey): void
{
    $role = Role::factory()->for($user->company)->create();
    $permission = Permission::factory()->create(['key' => $permissionKey]);

    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('allows platform users to manage SaaS abilities', function () {
    $user = User::factory()->create();

    expect(Gate::forUser($user)->allows('saas_settings.update'))->toBeTrue()
        ->and(Gate::forUser($user)->allows('plans.create'))->toBeTrue()
        ->and(Gate::forUser($user)->allows('company_add_ons.manage'))->toBeTrue();
});

it('keeps SaaS abilities outside tenant permission shortcuts', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantTenantPermission($user, 'plans.create');
    grantTenantPermission($user, 'employees.create');

    $this->actingAs($user);

    expect($user->hasPermission('employees.create'))->toBeTrue()
        ->and($user->hasPermission('plans.create'))->toBeFalse()
        ->and(Gate::forUser($user)->allows('plans.create'))->toBeFalse();
});
