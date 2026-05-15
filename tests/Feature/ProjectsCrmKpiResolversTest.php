<?php

use App\DTOs\KpiDateRange;
use App\Enums\LeadStatus;
use App\Enums\ProjectStatus;
use App\Enums\ProjectTaskStatus;
use App\Models\Company;
use App\Models\CrmLead;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTimeLog;
use App\Services\Kpis\Projects\ActiveProjectsKpi;
use App\Services\Kpis\Projects\BillableHoursKpi;
use App\Services\Kpis\Projects\CompletedProjectsKpi;
use App\Services\Kpis\Projects\LeadConversionKpi;
use App\Services\Kpis\Projects\LeadsByStatusKpi;
use App\Services\Kpis\Projects\OverdueTasksKpi;
use App\Services\Kpis\Projects\TotalLoggedHoursKpi;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('resolves project and CRM KPI values for the company', function () {
    $company = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create();
    $project = Project::factory()->for($company)->create(['status' => ProjectStatus::Active, 'created_at' => '2026-01-05 00:00:00']);
    Project::factory()->for($company)->create(['status' => ProjectStatus::Completed, 'created_at' => '2026-01-06 00:00:00']);
    ProjectTask::factory()->for($company)->for($project)->create(['status' => ProjectTaskStatus::InProgress, 'due_date' => now()->subDay()]);
    ProjectTimeLog::factory()->for($company)->for($project)->for($employee)->create(['log_date' => '2026-01-10', 'total_minutes' => 120, 'is_billable' => true]);
    ProjectTimeLog::factory()->for($company)->for($project)->for($employee)->create(['log_date' => '2026-01-11', 'total_minutes' => 60, 'is_billable' => false]);
    CrmLead::factory()->for($company)->create(['status' => LeadStatus::Converted, 'created_at' => '2026-01-12 00:00:00']);
    CrmLead::factory()->for($company)->create(['status' => LeadStatus::New, 'created_at' => '2026-01-13 00:00:00']);

    $range = KpiDateRange::fromDates('2026-01-01', '2026-01-31');

    expect(app(ActiveProjectsKpi::class)->resolve($company, $range)->value)->toBe(1)
        ->and(app(CompletedProjectsKpi::class)->resolve($company, $range)->value)->toBe(1)
        ->and(app(OverdueTasksKpi::class)->resolve($company, $range)->value)->toBe(1)
        ->and(app(TotalLoggedHoursKpi::class)->resolve($company, $range)->value)->toBe(3.0)
        ->and(app(BillableHoursKpi::class)->resolve($company, $range)->value)->toBe(2.0)
        ->and(app(LeadsByStatusKpi::class)->resolve($company, $range)->value)->toBe(2)
        ->and(app(LeadConversionKpi::class)->resolve($company, $range)->value)->toBe(50.0);
});
