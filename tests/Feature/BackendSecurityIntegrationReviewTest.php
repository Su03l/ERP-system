<?php

use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\SecuritySettingController;
use App\Http\Controllers\UserSessionController;
use App\Http\Controllers\WebhookDeliveryController;
use App\Http\Controllers\WebhookEndpointController;
use App\Models\CompanyApiToken;
use App\Models\WebhookEndpoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

it('keeps security management routes behind authenticated web middleware', function () {
    foreach ([
        'security-settings.index',
        'audit-logs.index',
        'api-tokens.index',
        'webhook-endpoints.index',
        'webhook-deliveries.index',
        'user-sessions.index',
    ] as $routeName) {
        expect(Route::getRoutes()->getByName($routeName)?->gatherMiddleware())->toContain('auth');
    }
});

it('keeps sensitive integration models hiding stored secrets by default', function () {
    expect((new CompanyApiToken)->getHidden())->toContain('token')
        ->and((new WebhookEndpoint)->getHidden())->toContain('secret_hash');
});

it('keeps security controllers thin and action oriented', function () {
    foreach ([
        ApiTokenController::class,
        AuditLogController::class,
        SecuritySettingController::class,
        UserSessionController::class,
        WebhookDeliveryController::class,
        WebhookEndpointController::class,
    ] as $controller) {
        $file = file_get_contents((new ReflectionClass($controller))->getFileName());

        expect($file)->not->toContain('DB::transaction')
            ->and($file)->not->toContain('hash(');
    }
});
