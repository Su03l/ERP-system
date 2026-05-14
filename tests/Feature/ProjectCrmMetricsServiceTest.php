<?php

use App\Enums\LeadStatus;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\ProjectTaskStatus;
use App\Models\Company;
use App\Models\CrmLead;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTimeLog;
use App\Services\ProjectCrmMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns tenant scoped project and CRM metrics with localized labels', function () {
    app()->setLocale('en');
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create();
    $activeProject = Project::factory()->for($company)->create(['status' => ProjectStatus::Active, 'priority' => ProjectPriority::High]);
    Project::factory()->for($company)->create(['status' => ProjectStatus::Completed]);
    Project::factory()->for($otherCompany)->create(['status' => ProjectStatus::Active]);
    ProjectTask::factory()->for($company)->create([
        'project_id' => $activeProject->id,
        'status' => ProjectTaskStatus::Todo,
        'due_date' => now()->subDay()->toDateString(),
    ]);
    ProjectTimeLog::factory()->for($company)->create([
        'project_id' => $activeProject->id,
        'employee_id' => $employee->id,
        'total_minutes' => 120,
        'is_billable' => true,
        'log_date' => '2026-05-14',
    ]);
    ProjectTimeLog::factory()->for($company)->create([
        'project_id' => $activeProject->id,
        'employee_id' => $employee->id,
        'total_minutes' => 30,
        'is_billable' => false,
        'log_date' => '2026-05-14',
    ]);
    CrmLead::factory()->for($company)->create(['status' => LeadStatus::New]);
    CrmLead::factory()->for($company)->create(['status' => LeadStatus::Converted]);
    CrmLead::factory()->for($otherCompany)->create(['status' => LeadStatus::New]);

    $metrics = app(ProjectCrmMetricsService::class)->forCompany($company, [
        'date_from' => '2026-05-01',
        'date_until' => '2026-05-31',
    ]);

    expect($metrics['company_id'])->toBe($company->id)
        ->and($metrics['metrics']['total_projects']['value'])->toBe(2)
        ->and($metrics['metrics']['active_projects']['value'])->toBe(1)
        ->and($metrics['metrics']['completed_projects']['value'])->toBe(1)
        ->and($metrics['metrics']['overdue_tasks']['value'])->toBe(1)
        ->and($metrics['metrics']['total_logged_hours']['value'])->toBe(2.5)
        ->and($metrics['metrics']['billable_hours']['value'])->toBe(2.0)
        ->and($metrics['metrics']['project_profitability']['metadata']['placeholder'])->toBeTrue()
        ->and($metrics['metrics']['lead_conversion']['metadata']['placeholder'])->toBeTrue()
        ->and(collect($metrics['groups']['leads_by_status'])->firstWhere('key', LeadStatus::New->value)['value'])->toBe(1)
        ->and(collect($metrics['groups']['leads_by_status'])->firstWhere('key', LeadStatus::Converted->value)['label'])->toBe('Converted');
});
