<?php

use App\Models\Company;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTimeLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('creates the project time logs schema', function () {
    expect(Schema::hasColumns('project_time_logs', [
        'id',
        'company_id',
        'project_id',
        'project_task_id',
        'employee_id',
        'log_date',
        'start_time',
        'end_time',
        'total_minutes',
        'is_billable',
        'notes_ar',
        'notes_en',
        'metadata',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

it('stores tenant scoped project time logs with project task and employee relationships', function () {
    $company = Company::factory()->create();
    $project = Project::factory()->for($company)->create();
    $task = ProjectTask::factory()->for($company)->create(['project_id' => $project->id]);
    $employee = Employee::factory()->for($company)->create();

    $timeLog = ProjectTimeLog::factory()->for($company)->create([
        'project_id' => $project->id,
        'project_task_id' => $task->id,
        'employee_id' => $employee->id,
        'log_date' => '2026-05-13',
        'total_minutes' => 90,
        'is_billable' => true,
        'metadata' => ['source' => 'manual'],
    ]);

    expect($timeLog->company->is($company))->toBeTrue()
        ->and($company->projectTimeLogs()->whereKey($timeLog)->exists())->toBeTrue()
        ->and($project->timeLogs()->whereKey($timeLog)->exists())->toBeTrue()
        ->and($task->timeLogs()->whereKey($timeLog)->exists())->toBeTrue()
        ->and($employee->projectTimeLogs()->whereKey($timeLog)->exists())->toBeTrue()
        ->and($timeLog->project->is($project))->toBeTrue()
        ->and($timeLog->projectTask->is($task))->toBeTrue()
        ->and($timeLog->employee->is($employee))->toBeTrue()
        ->and($timeLog->log_date->toDateString())->toBe('2026-05-13')
        ->and($timeLog->total_minutes)->toBe(90)
        ->and($timeLog->is_billable)->toBeTrue()
        ->and($timeLog->metadata)->toBe(['source' => 'manual']);
});

it('scopes project time logs to the current company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $project = Project::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $otherProject = Project::factory()->for($otherCompany)->create();
    $otherEmployee = Employee::factory()->for($otherCompany)->create();
    $timeLog = ProjectTimeLog::factory()->for($company)->create([
        'project_id' => $project->id,
        'employee_id' => $employee->id,
    ]);
    ProjectTimeLog::factory()->for($otherCompany)->create([
        'project_id' => $otherProject->id,
        'employee_id' => $otherEmployee->id,
    ]);

    $this->actingAs($user);

    expect(ProjectTimeLog::query()->forCurrentCompany()->pluck('id')->all())->toBe([$timeLog->id]);
});

it('prevents negative time log minutes', function () {
    $company = Company::factory()->create();
    $project = Project::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();

    ProjectTimeLog::factory()->for($company)->create([
        'project_id' => $project->id,
        'employee_id' => $employee->id,
        'total_minutes' => -1,
    ]);
})->throws(ValidationException::class);
