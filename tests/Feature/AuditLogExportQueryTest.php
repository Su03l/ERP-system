<?php

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogExportQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantAuditPermission(User $user, string $permissionKey): void
{
    $role = Role::factory()->for($user->company)->create();
    $permission = Permission::query()->firstOrCreate(['key' => $permissionKey], ['name' => $permissionKey]);
    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('exports only current company audit logs with filters and localized columns', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantAuditPermission($user, 'audit_logs.export');
    $this->actingAs($user);

    AuditLog::factory()->for($company)->for($user)->create([
        'action' => 'api_token.created',
        'ip_address' => '127.0.0.1',
    ]);
    AuditLog::factory()->for($otherCompany)->create([
        'action' => 'api_token.created',
        'ip_address' => '127.0.0.1',
    ]);

    $export = app(AuditLogExportQuery::class)->export([
        'action' => 'api_token',
        'ip_address' => '127.0.0.1',
    ], $user);

    expect($export['entity_type'])->toBe('audit_logs')
        ->and($export['module_key'])->toBe('security')
        ->and($export['rows'])->toHaveCount(1)
        ->and($export['rows'][0]['company_id'])->toBe($company->id)
        ->and($export['columns'][0]['label'])->not->toBeEmpty();
});
