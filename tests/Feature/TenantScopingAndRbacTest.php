<?php

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('tenant owned models can be scoped by company explicitly', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $role = Role::factory()->for($company)->create();

    Role::factory()->for($otherCompany)->create();

    expect(Role::forCompany($company)->get())->toHaveCount(1)
        ->and(Role::forCompany($company)->first()->is($role))->toBeTrue();
});

test('tenant owned models can be scoped by the current company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $role = Role::factory()->for($company)->create();

    Role::factory()->for($otherCompany)->create();

    $this->actingAs($user);

    expect(Role::forCurrentCompany()->get())->toHaveCount(1)
        ->and(Role::forCurrentCompany()->first()->is($role))->toBeTrue();
});

test('users can receive company scoped roles with permissions', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $role = Role::factory()->for($company)->create();
    $permission = Permission::factory()->create([
        'key' => 'users.create',
    ]);

    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $company->id]);

    expect($user->roles)->toHaveCount(1)
        ->and($user->roles->first()->is($role))->toBeTrue()
        ->and($role->permissions)->toHaveCount(1)
        ->and($role->permissions->first()->is($permission))->toBeTrue();
});
