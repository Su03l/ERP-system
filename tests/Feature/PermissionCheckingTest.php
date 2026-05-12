<?php

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

test('user can check permission by key inside current company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $role = Role::factory()->for($company)->create();
    $permission = Permission::factory()->create(['key' => 'users.invite']);

    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $company->id]);

    $this->actingAs($user);

    expect($user->hasPermission('users.invite'))->toBeTrue()
        ->and(Gate::forUser($user)->allows('users.invite'))->toBeTrue();
});

test('permission checks fail outside the users company context', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $role = Role::factory()->for($otherCompany)->create();
    $permission = Permission::factory()->create(['key' => 'payroll_runs.generate']);

    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $otherCompany->id]);

    $this->actingAs($user);

    expect($user->hasPermission('payroll_runs.generate'))->toBeFalse()
        ->and(Gate::forUser($user)->allows('payroll_runs.generate'))->toBeFalse();
});

test('permission checks fail when no current company is available', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    expect($user->hasPermission('users.invite'))->toBeFalse();
});
