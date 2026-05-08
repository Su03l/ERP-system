<?php

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('audit logger records critical action context', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();

    $this->actingAs($user)
        ->withHeader('User-Agent', 'Nawwat Test Browser')
        ->get('/');

    $auditLog = app(AuditLogger::class)->log(
        action: 'users.invited',
        auditable: $user,
        oldValues: ['status' => 'pending'],
        newValues: ['status' => 'active'],
        metadata: ['source' => 'test'],
    );

    expect($auditLog)->toBeInstanceOf(AuditLog::class)
        ->and($auditLog->company_id)->toBe($company->id)
        ->and($auditLog->user_id)->toBe($user->id)
        ->and($auditLog->action)->toBe('users.invited')
        ->and($auditLog->auditable_type)->toBe($user->getMorphClass())
        ->and($auditLog->auditable_id)->toBe($user->id)
        ->and($auditLog->old_values)->toBe(['status' => 'pending'])
        ->and($auditLog->new_values)->toBe(['status' => 'active'])
        ->and($auditLog->metadata)->toBe(['source' => 'test']);
});

test('audit logger supports system actions without company or user', function () {
    $auditLog = app(AuditLogger::class)->log(
        action: 'system.health_checked',
        company: null,
        user: null,
    );

    expect($auditLog->company_id)->toBeNull()
        ->and($auditLog->user_id)->toBeNull()
        ->and($auditLog->action)->toBe('system.health_checked');
});
