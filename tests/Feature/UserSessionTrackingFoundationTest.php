<?php

use App\Actions\RevokeUserSession;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserSession;
use App\Services\UserSessionQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('tracks and lists tenant user sessions without plain session secrets', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $session = UserSession::factory()->for($company)->for($user)->create();
    UserSession::factory()->create();

    $rows = app(UserSessionQuery::class)->rows($user);

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['user_id'])->toBe($user->id)
        ->and($user->trackedSessions()->first()->is($session))->toBeTrue()
        ->and($company->userSessions()->first()->is($session))->toBeTrue();
});

it('revokes user sessions with permission checks', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $role = Role::factory()->for($company)->create();
    $permission = Permission::query()->firstOrCreate(['key' => 'user_sessions.revoke'], ['name' => 'user_sessions.revoke']);
    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $company->id]);
    $session = UserSession::factory()->for($company)->for($user)->create();

    $revoked = app(RevokeUserSession::class)->handle($session, $user);

    expect($revoked->isRevoked())->toBeTrue();
});
