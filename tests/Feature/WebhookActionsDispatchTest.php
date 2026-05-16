<?php

use App\Actions\CreateWebhookEndpoint;
use App\Actions\DeleteWebhookEndpoint;
use App\Actions\UpdateWebhookEndpoint;
use App\Jobs\DispatchWebhookDelivery;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\WebhookEndpoint;
use App\Services\WebhookDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function grantWebhookPermission(User $user, string $permissionKey): void
{
    $role = Role::factory()->for($user->company)->create();
    $permission = Permission::query()->firstOrCreate(['key' => $permissionKey], ['name' => $permissionKey]);
    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('creates updates and deletes webhook endpoints with hashed secrets and audit logs', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantWebhookPermission($user, 'webhooks.create');
    grantWebhookPermission($user, 'webhooks.update');
    grantWebhookPermission($user, 'webhooks.delete');
    $this->actingAs($user);

    $endpoint = app(CreateWebhookEndpoint::class)->handle([
        'name' => 'Billing webhook',
        'url' => 'https://example.test/webhook',
        'secret' => 'super-secret-value',
        'events' => ['invoice.paid'],
    ], $user);

    expect($endpoint->secret_hash)->toBe(hash('sha256', 'super-secret-value'))
        ->and(AuditLog::query()->where('action', 'webhook_endpoint.created')->exists())->toBeTrue();

    $updated = app(UpdateWebhookEndpoint::class)->handle($endpoint, [
        'status' => 'inactive',
        'events' => ['invoice.cancelled'],
    ], $user);

    expect($updated->status)->toBe('inactive')
        ->and($updated->events)->toBe(['invoice.cancelled']);

    app(DeleteWebhookEndpoint::class)->handle($updated, $user);

    expect(WebhookEndpoint::query()->count())->toBe(0)
        ->and(AuditLog::query()->where('action', 'webhook_endpoint.deleted')->exists())->toBeTrue();
});

it('creates queued webhook deliveries and removes sensitive payload keys', function () {
    Queue::fake();
    $endpoint = WebhookEndpoint::factory()->create();

    $delivery = app(WebhookDeliveryService::class)->createDelivery($endpoint, 'invoice.paid', [
        'invoice_id' => 5,
        'token' => 'do-not-store',
    ]);
    app(WebhookDeliveryService::class)->queue($delivery);

    expect($delivery->payload)->toBe(['invoice_id' => 5])
        ->and($delivery->status)->toBe('pending');

    Queue::assertPushed(DispatchWebhookDelivery::class);
});

it('delivers webhook payloads with signatures and status tracking', function () {
    Http::fake([
        'https://example.test/webhook' => Http::response(['ok' => true], 200),
    ]);
    $endpoint = WebhookEndpoint::factory()->create([
        'url' => 'https://example.test/webhook',
        'secret_hash' => hash('sha256', 'secret'),
    ]);
    $delivery = app(WebhookDeliveryService::class)->createDelivery($endpoint, 'invoice.paid', ['invoice_id' => 5]);

    $delivered = app(WebhookDeliveryService::class)->deliver($delivery);

    expect($delivered->status)->toBe('delivered')
        ->and($delivered->response_status)->toBe(200)
        ->and($delivered->endpoint->refresh()->last_success_at)->not->toBeNull();

    Http::assertSent(fn ($request): bool => $request->hasHeader('X-Nawwat-Signature'));
});
