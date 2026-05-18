<?php

use App\Actions\CreateApiToken;
use App\Actions\CreateWebhookEndpoint;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\CompanyApiToken;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SecuritySetting;
use App\Models\User;
use App\Services\SecurityExportService;
use App\Services\WebhookDeliveryService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function grantSecurityCoveragePermissions219(User $user, array $permissionKeys): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissionKeys as $permissionKey) {
        $permission = Permission::query()->firstOrCreate(
            ['key' => $permissionKey],
            ['name' => $permissionKey, 'description' => null],
        );

        $role->permissions()->syncWithoutDetaching([$permission->id]);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

test('api tokens are hashed and plain tokens are only returned by creation result', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantSecurityCoveragePermissions219($user, ['api_tokens.create']);
    Permission::query()->firstOrCreate(['key' => 'public-api.read'], ['name' => 'public-api.read']);

    $this->actingAs($user);

    $result = app(CreateApiToken::class)->handle([
        'name' => 'Public API token',
        'abilities' => ['public-api.read'],
    ], $user);

    $storedToken = $result['token']->refresh();

    expect($result['plain_text_token'])->toHaveLength(64)
        ->and($storedToken->token)->toBe(hash('sha256', $result['plain_text_token']))
        ->and($storedToken->toArray())->not->toHaveKey('token')
        ->and($storedToken->getAttribute('plain_text_token'))->toBeNull()
        ->and(AuditLog::query()->where('action', 'api_token.created')->exists())->toBeTrue();
});

test('public api rejects revoked expired and under scoped tokens', function () {
    $company = Company::factory()->create();
    $revoked = Str::random(64);
    $expired = Str::random(64);
    $wrongAbility = Str::random(64);

    CompanyApiToken::factory()->for($company)->create([
        'token' => hash('sha256', $revoked),
        'abilities' => ['public-api.read'],
        'revoked_at' => now(),
    ]);
    CompanyApiToken::factory()->for($company)->create([
        'token' => hash('sha256', $expired),
        'abilities' => ['public-api.read'],
        'expires_at' => now()->subMinute(),
    ]);
    CompanyApiToken::factory()->for($company)->create([
        'token' => hash('sha256', $wrongAbility),
        'abilities' => ['customers.read'],
    ]);

    $this->withToken($revoked)->getJson(route('public-api.company.show'))->assertUnauthorized();
    $this->withToken($expired)->getJson(route('public-api.company.show'))->assertUnauthorized();
    $this->withToken($wrongAbility)->getJson(route('public-api.company.show'))->assertForbidden();
});

test('webhook endpoint creation hashes secrets and delivery failures are recorded', function () {
    Http::fake([
        'https://example.test/webhook' => Http::response('failure', 500),
    ]);

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantSecurityCoveragePermissions219($user, ['webhooks.create']);

    $this->actingAs($user);

    $endpoint = app(CreateWebhookEndpoint::class)->handle([
        'name' => 'Failure webhook',
        'url' => 'https://example.test/webhook',
        'secret' => Str::random(40),
        'events' => ['customer.created'],
    ], $user);
    $delivery = app(WebhookDeliveryService::class)->createDelivery($endpoint, 'customer.created', [
        'customer_id' => 15,
        'token' => Str::random(32),
    ]);
    $failed = app(WebhookDeliveryService::class)->deliver($delivery);

    expect($endpoint->secret_hash)->not->toBeNull()
        ->and($delivery->payload)->toBe(['customer_id' => 15])
        ->and($failed->status)->toBe('failed')
        ->and($failed->response_status)->toBe(500)
        ->and($failed->attempt_count)->toBe(1)
        ->and($endpoint->refresh()->failure_count)->toBe(1);
});

test('sensitive audit exports require permission and approval when configured', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    SecuritySetting::factory()->for($company)->create(['export_approval_required' => true]);

    $this->actingAs($user);

    app(SecurityExportService::class)->auditLogs($user);
})->throws(AuthorizationException::class);

test('audit log exports require explicit permission and omit other tenant logs', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantSecurityCoveragePermissions219($user, ['audit_logs.export', 'exports.approve_sensitive']);
    AuditLog::factory()->for($company)->for($user)->create(['action' => 'api_token.created']);
    AuditLog::factory()->for($otherCompany)->create(['action' => 'api_token.created']);

    $this->actingAs($user);

    $export = app(SecurityExportService::class)->auditLogs($user, ['action' => 'api_token']);

    expect($export['entity_type'])->toBe('audit_logs')
        ->and($export['rows'])->toHaveCount(1)
        ->and($export['rows'][0]['company_id'])->toBe($company->id);
});
