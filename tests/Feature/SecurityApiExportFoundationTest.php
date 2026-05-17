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
use App\Notifications\SecurityEventNotification;
use App\Services\SecurityExportService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantSecurityExportPermission198(User $user, string $permissionKey): void
{
    $role = Role::factory()->for($user->company)->create();
    $permission = Permission::query()->firstOrCreate(['key' => $permissionKey], ['name' => $permissionKey]);
    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('exports security integration data without secrets', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    foreach (['api_tokens.view', 'webhooks.view', 'user_sessions.view', 'security_settings.view'] as $permission) {
        grantSecurityExportPermission198($user, $permission);
    }

    CompanyApiToken::factory()->for($company)->for($user)->create(['name' => 'External API', 'token' => 'stored-hash']);
    $endpoint = WebhookEndpoint::factory()->for($company)->create(['secret_hash' => 'stored-secret']);
    WebhookDelivery::factory()->for($company)->for($endpoint, 'endpoint')->create(['status' => 'failed']);
    UserSession::factory()->for($company)->for($user)->create(['session_id' => 'session-hash']);
    $user->notify(new SecurityEventNotification('api_token.created', ['company_id' => $company->id]));

    $exports = app(SecurityExportService::class);

    expect($exports->apiTokens($user)['rows'][0])->not->toHaveKeys(['token', 'secret_hash'])
        ->and($exports->webhookDeliveries($user)['rows'][0])->not->toHaveKey('payload')
        ->and($exports->userSessions($user)['rows'][0])->not->toHaveKey('session_id')
        ->and($exports->securityEvents($user)['rows'][0]['event'])->toBe('api_token.created');
});

it('requires direct approval permission before exporting sensitive audit logs', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantSecurityExportPermission198($user, 'audit_logs.export');
    SecuritySetting::factory()->for($company)->create(['export_approval_required' => true]);
    AuditLog::factory()->for($company)->for($user)->create(['action' => 'api_token.revoked']);
    $this->actingAs($user);

    expect(fn () => app(SecurityExportService::class)->auditLogs($user))
        ->toThrow(AuthorizationException::class);

    grantSecurityExportPermission198($user, 'exports.approve_sensitive');

    expect(app(SecurityExportService::class)->auditLogs($user)['rows'][0]['action'])->toBe('api_token.revoked');
});
