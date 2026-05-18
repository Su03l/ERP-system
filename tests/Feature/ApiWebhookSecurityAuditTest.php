<?php

use App\Jobs\DispatchWebhookDelivery;
use App\Models\CompanyApiToken;
use App\Models\WebhookEndpoint;
use App\Services\PublicApiTokenAuthenticator;
use App\Services\WebhookDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

it('keeps API tokens hashed hidden and ability based', function () {
    $plainToken = 'api-audit-token';
    $token = CompanyApiToken::factory()->create([
        'token' => hash('sha256', $plainToken),
        'abilities' => ['public-api.read'],
    ]);

    expect($token->toArray())->not->toHaveKey('token')
        ->and(app(PublicApiTokenAuthenticator::class)->tokenFromRequest(Request::create('/', server: ['HTTP_AUTHORIZATION' => "Bearer {$plainToken}"]))?->is($token))->toBeTrue()
        ->and($token->can('public-api.read'))->toBeTrue()
        ->and($token->can('payroll.read'))->toBeFalse();
});

it('keeps public API routes rate limited behind token middleware', function () {
    $middleware = Route::getRoutes()->getByName('public-api.company.show')?->gatherMiddleware() ?? [];

    expect($middleware)->toContain('company.api:public-api.read')
        ->and($middleware)->toContain('company.api.throttle:120,60');
});

it('queues webhook delivery after commit and keeps secrets hidden', function () {
    Queue::fake();
    $endpoint = WebhookEndpoint::factory()->create(['secret_hash' => hash('sha256', 'secret')]);
    $delivery = app(WebhookDeliveryService::class)->createDelivery($endpoint, 'customer.created', [
        'id' => 1,
        'secret' => 'hidden',
        'nested' => ['access_token' => 'hidden'],
    ]);

    app(WebhookDeliveryService::class)->queue($delivery);

    expect($endpoint->toArray())->not->toHaveKey('secret_hash')
        ->and($delivery->payload)->toBe(['id' => 1, 'nested' => []]);

    Queue::assertPushed(DispatchWebhookDelivery::class);
});

it('keeps webhook external calls bounded with timeouts', function () {
    $contents = file_get_contents((new ReflectionClass(WebhookDeliveryService::class))->getFileName());

    expect($contents)->toContain('Http::timeout(10)')
        ->and($contents)->not->toContain('catch (Throwable');
});
