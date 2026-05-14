<?php

use App\Enums\ContactStatus;
use App\Enums\LeadStatus;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\ProjectTaskStatus;
use App\Models\Company;
use App\Models\CrmContact;
use App\Models\CrmLead;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTimeLog;
use App\Services\ProjectCrmExportQuery;
use App\Services\ProjectCrmImportDefinitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('provides import definitions with validation placeholders', function () {
    $definitions = app(ProjectCrmImportDefinitionService::class)->definitions();

    expect($definitions)->toHaveKeys(['projects', 'project_tasks', 'crm_leads', 'crm_contacts', 'project_time_logs'])
        ->and($definitions['projects']['columns'])->toContain('code', 'name_ar')
        ->and($definitions['project_time_logs']['validation']['placeholder'])->toBeTrue();
});

it('exports project and CRM data as tenant scoped arrays with localized labels', function () {
    app()->setLocale('en');
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create(['first_name_ar' => 'سارة', 'last_name_ar' => 'خالد']);
    $project = Project::factory()->for($company)->create([
        'project_manager_id' => $employee->id,
        'status' => ProjectStatus::Active,
        'priority' => ProjectPriority::High,
    ]);
    $task = ProjectTask::factory()->for($company)->create([
        'project_id' => $project->id,
        'assigned_employee_id' => $employee->id,
        'status' => ProjectTaskStatus::InProgress,
        'priority' => ProjectPriority::High,
    ]);
    ProjectTimeLog::factory()->for($company)->create([
        'project_id' => $project->id,
        'project_task_id' => $task->id,
        'employee_id' => $employee->id,
        'total_minutes' => 45,
        'is_billable' => true,
    ]);
    CrmLead::factory()->for($company)->create(['status' => LeadStatus::Qualified]);
    CrmContact::factory()->for($company)->create(['status' => ContactStatus::Active]);
    Project::factory()->for($otherCompany)->create();

    $export = app(ProjectCrmExportQuery::class);

    expect($export->projects($company))->toHaveCount(1)
        ->and($export->projects($company)[0]['status_label'])->toBe('Active')
        ->and($export->projectTasks($company))->toHaveCount(1)
        ->and($export->projectTasks($company)[0]['priority_label'])->toBe('High')
        ->and($export->leads($company)[0]['status_label'])->toBe('Qualified')
        ->and($export->contacts($company)[0]['status_label'])->toBe('Active')
        ->and($export->timeLogs($company)[0]['total_minutes'])->toBe(45);
});
