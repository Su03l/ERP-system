<?php

use App\DTOs\ReportFilter;
use App\Jobs\ProcessReportExportJob;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\ReportExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function grantReportPermission(User $user, string $permissionKey): void
{
    $role = Role::factory()->for($user->company)->create();
    $permission = Permission::query()->firstOrCreate(['key' => $permissionKey], ['name' => $permissionKey]);
    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('creates a queue-ready export job for report exports', function () {
    Queue::fake();
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();

    $job = app(ReportExportService::class)->request(
        'hr.employees',
        ReportFilter::fromArray(['export_format' => 'csv'], $company->id),
        $user,
    );

    expect($job->status)->toBe('pending')
        ->and($job->company_id)->toBe($company->id)
        ->and($job->entity_type)->toBe('hr.employees');

    Queue::assertPushed(ProcessReportExportJob::class);
});

it('processes a report export into tenant-scoped local storage', function () {
    Storage::fake('local');
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantReportPermission($user, 'employees.view');
    Employee::factory()->for($company)->create(['employee_number' => 'E-001']);

    $job = app(ReportExportService::class)->request(
        'hr.employees',
        ReportFilter::fromArray(['export_format' => 'csv'], $company->id),
        $user,
        queued: false,
    );

    expect($job->status)->toBe('completed')
        ->and($job->file_path)->toStartWith("exports/reports/{$company->id}/")
        ->and($job->processed_rows)->toBe(1);

    Storage::disk('local')->assertExists($job->file_path);
});
