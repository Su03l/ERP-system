<?php

use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds strict security audit token session and webhook permissions', function () {
    $this->artisan('migrate');

    foreach ([
        'security_settings.view',
        'security_settings.update',
        'audit_logs.view',
        'audit_logs.export',
        'user_sessions.view',
        'user_sessions.revoke',
        'api_tokens.view',
        'api_tokens.create',
        'api_tokens.revoke',
        'webhooks.view',
        'webhooks.create',
        'webhooks.update',
        'webhooks.delete',
    ] as $permissionKey) {
        expect(Permission::query()->where('key', $permissionKey)->exists())->toBeTrue();
    }
});
