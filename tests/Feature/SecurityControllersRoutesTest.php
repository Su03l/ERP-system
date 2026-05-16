<?php

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\CompanyApiToken;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SecuritySetting;
use App\Models\User;
use App\Models\UserSession;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantSecurityRoutePermission196(User $user, string $permissionKey): void
{
    $role = Role::factory()->for($user->company)->create();
    $permission = Permission::query()->firstOrCreate(['key' => $permissionKey], ['name' => $permissionKey]);
    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('protects security routes behind explicit permissions', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();

    $this->actingAs($user)
        ->getJson(route('security-settings.index'))
        ->assertForbidden();

    grantSecurityRoutePermission196($user, 'security_settings.view');

    $this->getJson(route('security-settings.index'))
        ->assertSuccessful()
        ->assertJsonPath('data.company_id', $company->id);
});

it('updates security settings through a thin controller action', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantSecurityRoutePermission196($user, 'security_settings.update');
    SecuritySetting::factory()->for($company)->create(['export_approval_required' => false]);

    $this->actingAs($user)
        ->putJson(route('security-settings.update', SecuritySetting::query()->first()), [
            'export_approval_required' => true,
            'audit_retention_days' => 730,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.export_approval_required', true);

    expect(AuditLog::query()->where('action', 'security_settings.updated')->exists())->toBeTrue();
});

it('lists security tools without exposing stored secrets', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    foreach (['api_tokens.view', 'webhooks.view', 'user_sessions.view', 'audit_logs.view'] as $permission) {
        grantSecurityRoutePermission196($user, $permission);
    }

    CompanyApiToken::factory()->for($company)->for($user)->create(['token' => 'hashed-token']);
    $endpoint = WebhookEndpoint::factory()->for($company)->create(['secret_hash' => 'hashed-secret']);
    WebhookDelivery::factory()->for($company)->for($endpoint, 'endpoint')->create(['payload' => ['ok' => true]]);
    UserSession::factory()->for($company)->for($user)->create(['session_id' => 'hashed-session']);
    AuditLog::factory()->for($company)->for($user)->create(['action' => 'api_token.created']);

    $this->actingAs($user);

    $this->getJson(route('api-tokens.index'))
        ->assertSuccessful()
        ->assertJsonMissing(['token' => 'hashed-token']);

    $this->getJson(route('webhook-endpoints.index'))
        ->assertSuccessful()
        ->assertJsonMissing(['secret_hash' => 'hashed-secret']);

    $this->getJson(route('webhook-deliveries.index'))
        ->assertSuccessful()
        ->assertJsonPath('data.0.payload.ok', true);

    $this->getJson(route('user-sessions.index'))
        ->assertSuccessful()
        ->assertJsonMissing(['session_id' => 'hashed-session']);

    $this->getJson(route('audit-logs.index'))
        ->assertSuccessful()
        ->assertJsonPath('data.0.action', 'api_token.created');
});
