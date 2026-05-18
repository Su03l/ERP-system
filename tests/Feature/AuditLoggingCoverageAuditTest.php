<?php

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SecuritySetting;
use App\Models\User;
use App\Services\SecurityExportService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantAuditCoveragePermission204(User $user, string $permissionKey): void
{
    $role = Role::factory()->for($user->company)->create();
    $permission = Permission::query()->firstOrCreate(['key' => $permissionKey], ['name' => $permissionKey]);
    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('audits sensitive export requests even when approval is required', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantAuditCoveragePermission204($user, 'audit_logs.export');
    SecuritySetting::factory()->for($company)->create(['export_approval_required' => true]);
    $this->actingAs($user);

    expect(fn () => app(SecurityExportService::class)->auditLogs($user))
        ->toThrow(AuthorizationException::class);

    expect(AuditLog::query()->where('action', 'sensitive_export.requested')->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'sensitive_export.approval_required')->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'sensitive_export.requested')->first()->new_values)
        ->toBe(['export_key' => 'audit_logs']);
});

it('keeps critical action audit labels covered by existing audit log actions', function (string $action) {
    $auditedActions = [
        'api_token.created',
        'api_token.revoked',
        'security_settings.updated',
        'webhook_endpoint.created',
        'webhook_endpoint.updated',
        'webhook_endpoint.deleted',
        'sensitive_export.requested',
        'sensitive_export.approval_required',
    ];

    expect($auditedActions)->toContain($action);
})->with([
    'api_token.created',
    'api_token.revoked',
    'security_settings.updated',
    'webhook_endpoint.created',
    'webhook_endpoint.updated',
    'webhook_endpoint.deleted',
    'sensitive_export.requested',
    'sensitive_export.approval_required',
]);
