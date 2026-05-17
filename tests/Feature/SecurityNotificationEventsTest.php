<?php

use App\Actions\CreateApiToken;
use App\Actions\RevokeApiToken;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\WebhookEndpoint;
use App\Notifications\SecurityEventNotification;
use App\Services\SecurityNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;

uses(RefreshDatabase::class);

function grantSecurityNotificationPermission197(User $user, string $permissionKey): void
{
    $role = Role::factory()->for($user->company)->create();
    $permission = Permission::query()->firstOrCreate(['key' => $permissionKey], ['name' => $permissionKey]);
    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('stores security events as database notifications', function () {
    $user = User::factory()->create();

    $user->notify(new SecurityEventNotification('subscription.access_blocked', [
        'company_id' => $user->company_id,
        'reason' => 'expired',
    ]));

    $notification = DatabaseNotification::query()->first();

    expect($notification)->not->toBeNull()
        ->and($notification->data['type'])->toBe('security_event')
        ->and($notification->data['event'])->toBe('subscription.access_blocked');
});

it('notifies API token create and revoke events without exposing token hashes', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    Permission::query()->firstOrCreate(['key' => 'public-api.read'], ['name' => 'public-api.read']);
    grantSecurityNotificationPermission197($user, 'api_tokens.create');
    grantSecurityNotificationPermission197($user, 'api_tokens.revoke');
    $this->actingAs($user);

    $result = app(CreateApiToken::class)->handle([
        'name' => 'Integration token',
        'abilities' => ['public-api.read'],
    ], $user);
    app(RevokeApiToken::class)->handle($result['token'], $user);

    $events = DatabaseNotification::query()->pluck('data')->pluck('event')->all();

    expect($events)->toContain('api_token.created')
        ->and($events)->toContain('api_token.revoked')
        ->and(DatabaseNotification::query()->get()->flatMap(fn (DatabaseNotification $notification) => array_keys($notification->data))->all())
        ->not->toContain('token');
});

it('prepares non-spam webhook failure threshold notifications', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantSecurityNotificationPermission197($user, 'webhooks.view');
    $endpoint = WebhookEndpoint::factory()->for($company)->create(['failure_count' => 3]);

    app(SecurityNotificationService::class)->webhookFailureThresholdReached($endpoint);
    app(SecurityNotificationService::class)->webhookFailureThresholdReached($endpoint);

    expect(DatabaseNotification::query()->count())->toBe(1)
        ->and(DatabaseNotification::query()->first()->data['event'])->toBe('webhook.failure_threshold_reached');
});
