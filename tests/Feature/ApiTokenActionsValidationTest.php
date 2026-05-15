<?php

use App\Actions\CreateApiToken;
use App\Actions\RevokeApiToken;
use App\Http\Requests\StoreApiTokenRequest;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\CompanyApiToken;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

function grantApiTokenPermission(User $user, string $permissionKey): void
{
    $role = Role::factory()->for($user->company)->create();
    $permission = Permission::query()->firstOrCreate(['key' => $permissionKey], ['name' => $permissionKey]);
    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('creates API tokens with one-time plain token output and hashed storage', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    Permission::query()->firstOrCreate(['key' => 'reports.export'], ['name' => 'reports.export']);
    grantApiTokenPermission($user, 'api_tokens.create');
    $this->actingAs($user);

    $result = app(CreateApiToken::class)->handle([
        'name' => 'Reporting token',
        'abilities' => ['reports.export'],
    ], $user);

    expect($result['plain_text_token'])->toHaveLength(64)
        ->and($result['token'])->toBeInstanceOf(CompanyApiToken::class)
        ->and($result['token']->token)->toBe(hash('sha256', $result['plain_text_token']))
        ->and($result['token']->company_id)->toBe($company->id)
        ->and(AuditLog::query()->where('action', 'api_token.created')->exists())->toBeTrue();
});

it('revokes API tokens with audit logging', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantApiTokenPermission($user, 'api_tokens.revoke');
    $this->actingAs($user);
    $token = CompanyApiToken::factory()->for($company)->for($user)->create();

    $revoked = app(RevokeApiToken::class)->handle($token, $user);

    expect($revoked->isRevoked())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'api_token.revoked')->exists())->toBeTrue();
});

it('validates API token abilities against registered permissions', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantApiTokenPermission($user, 'api_tokens.create');

    $request = StoreApiTokenRequest::create('/api-tokens', 'POST', [
        'name' => 'Invalid token',
        'abilities' => ['missing.permission'],
    ]);
    $request->setUserResolver(fn () => $user);

    $validator = Validator::make($request->all(), $request->rules());

    foreach ($request->after() as $after) {
        $validator->after($after);
    }

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('abilities'))->toBeTrue();
});
