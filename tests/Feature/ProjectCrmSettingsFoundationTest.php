<?php

use App\Enums\ProjectStatus;
use App\Models\Company;
use App\Models\ProjectCrmSetting;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates the project and CRM settings schema', function () {
    expect(Schema::hasColumns('project_crm_settings', [
        'company_id',
        'project_code_prefix',
        'task_code_prefix',
        'default_project_status',
        'project_approval_required',
        'task_approval_required',
        'time_tracking_enabled',
        'billable_time_enabled',
        'crm_enabled',
        'metadata',
    ]))->toBeTrue();
});

it('stores tenant scoped project and CRM settings', function () {
    $company = Company::factory()->create();

    $setting = ProjectCrmSetting::factory()->for($company)->create([
        'metadata' => ['managed_by' => 'projects_crm'],
    ]);

    expect($setting->company->is($company))->toBeTrue()
        ->and($company->projectCrmSetting->is($setting))->toBeTrue()
        ->and($setting->project_code_prefix)->toBe('PRJ')
        ->and($setting->task_code_prefix)->toBe('TSK')
        ->and($setting->default_project_status)->toBe(ProjectStatus::Draft)
        ->and($setting->project_approval_required)->toBeTrue()
        ->and($setting->task_approval_required)->toBeFalse()
        ->and($setting->time_tracking_enabled)->toBeTrue()
        ->and($setting->billable_time_enabled)->toBeFalse()
        ->and($setting->crm_enabled)->toBeTrue()
        ->and($setting->metadata)->toBe(['managed_by' => 'projects_crm']);
});

it('keeps project and CRM settings one to one per company', function () {
    $company = Company::factory()->create();

    ProjectCrmSetting::factory()->for($company)->create();
    ProjectCrmSetting::factory()->for($company)->create();
})->throws(QueryException::class);
