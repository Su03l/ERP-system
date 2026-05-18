<?php

use App\DTOs\ReportFilter;
use App\Models\Company;
use App\Models\ExportJob;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SecuritySetting;
use App\Models\User;
use App\Services\ReportExportService;
use App\Services\ReportSpreadsheetExportService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantExportAuditPermission209(User $user, string $permissionKey): void
{
    $role = Role::factory()->for($user->company)->create();
    $permission = Permission::query()->firstOrCreate(['key' => $permissionKey], ['name' => $permissionKey]);
    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('requires report specific export permission before creating export jobs', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantExportAuditPermission209($user, 'reports.export');

    expect(fn () => app(ReportExportService::class)->request('payroll.runs', new ReportFilter(companyId: $company->id, exportFormat: 'csv'), $user))
        ->toThrow(AuthorizationException::class);

    expect(ExportJob::query()->count())->toBe(0);
});

it('requires sensitive export approval when company settings demand it', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    SecuritySetting::factory()->for($company)->create(['export_approval_required' => true]);
    grantExportAuditPermission209($user, 'payroll_runs.export');

    expect(fn () => app(ReportExportService::class)->request('payroll.runs', new ReportFilter(companyId: $company->id, exportFormat: 'csv'), $user))
        ->toThrow(AuthorizationException::class);
});

it('uses safe private local export paths and filenames', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantExportAuditPermission209($user, 'payroll_runs.export');
    grantExportAuditPermission209($user, 'exports.approve_sensitive');

    $job = app(ReportExportService::class)->request('payroll.runs', new ReportFilter(companyId: $company->id, exportFormat: 'csv'), $user, queued: false);

    expect($job->file_path)->toStartWith("exports/reports/{$company->id}/")
        ->and($job->file_path)->not->toContain('..')
        ->and($job->file_path)->not->toStartWith('public/');
});

it('normalizes unsafe report file names', function () {
    $fileName = app(ReportSpreadsheetExportService::class)->safeFileName('../payroll\\runs', 'csv', '20260518_100000');

    expect($fileName)->toBe('payroll-runs-20260518_100000.csv');
});
