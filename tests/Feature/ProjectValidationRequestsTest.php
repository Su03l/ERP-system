<?php

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\ProjectTaskStatus;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\StoreProjectTaskRequest;
use App\Http\Requests\StoreProjectTimeLogRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Requests\UpdateProjectTaskRequest;
use App\Http\Requests\UpdateProjectTimeLogRequest;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTimeLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Route::post('/test/projects', fn (StoreProjectRequest $request) => $request->validated());
    Route::patch('/test/projects/{project}', fn (UpdateProjectRequest $request, Project $project) => $request->validated());
    Route::post('/test/project-tasks', fn (StoreProjectTaskRequest $request) => $request->validated());
    Route::patch('/test/project-tasks/{project_task}', fn (UpdateProjectTaskRequest $request, ProjectTask $projectTask) => $request->validated());
    Route::post('/test/project-time-logs', fn (StoreProjectTimeLogRequest $request) => $request->validated());
    Route::patch('/test/project-time-logs/{project_time_log}', fn (UpdateProjectTimeLogRequest $request, ProjectTimeLog $projectTimeLog) => $request->validated());
});

it('validates project payloads against tenant scoped relations and enums', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $otherCustomer = Customer::factory()->for(Company::factory())->create();
    $otherEmployee = Employee::factory()->for(Company::factory())->create();

    $this->actingAs($actor)
        ->postJson('/test/projects', [
            'company_id' => $company->id,
            'customer_id' => $otherCustomer->id,
            'project_manager_id' => $otherEmployee->id,
            'code' => 'PRJ-1',
            'name_ar' => 'مشروع',
            'start_date' => '2026-05-13',
            'end_date' => '2026-05-12',
            'status' => 'bad',
            'priority' => 'bad',
            'progress_percentage' => 101,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['company_id', 'customer_id', 'project_manager_id', 'end_date', 'status', 'priority', 'progress_percentage']);

    $this->actingAs($actor)
        ->postJson('/test/projects', [
            'code' => 'PRJ-1',
            'name_ar' => 'مشروع',
            'status' => ProjectStatus::Draft->value,
            'priority' => ProjectPriority::Medium->value,
            'progress_percentage' => 50,
        ])
        ->assertSuccessful();
});

it('validates project task payloads against tenant and project relations', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $project = Project::factory()->for($company)->create();
    $otherProject = Project::factory()->for(Company::factory())->create();
    $otherEmployee = Employee::factory()->for(Company::factory())->create();
    $parentFromAnotherProject = ProjectTask::factory()->for($company)->create(['project_id' => Project::factory()->for($company)->create()->id]);

    $this->actingAs($actor)
        ->postJson('/test/project-tasks', [
            'project_id' => $otherProject->id,
            'assigned_employee_id' => $otherEmployee->id,
            'parent_task_id' => $parentFromAnotherProject->id,
            'title_ar' => 'مهمة',
            'start_date' => '2026-05-13',
            'due_date' => '2026-05-12',
            'status' => 'bad',
            'priority' => 'bad',
            'progress_percentage' => -1,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['project_id', 'assigned_employee_id', 'parent_task_id', 'due_date', 'status', 'priority', 'progress_percentage']);

    $this->actingAs($actor)
        ->postJson('/test/project-tasks', [
            'project_id' => $project->id,
            'title_ar' => 'مهمة',
            'status' => ProjectTaskStatus::Todo->value,
            'priority' => ProjectPriority::Medium->value,
            'progress_percentage' => 0,
        ])
        ->assertSuccessful();
});

it('validates project time logs against tenant relations date ranges and minutes', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $project = Project::factory()->for($company)->create();
    $task = ProjectTask::factory()->for($company)->create(['project_id' => $project->id]);
    $employee = Employee::factory()->for($company)->create();
    $otherEmployee = Employee::factory()->for(Company::factory())->create();
    $taskFromAnotherProject = ProjectTask::factory()->for($company)->create(['project_id' => Project::factory()->for($company)->create()->id]);

    $this->actingAs($actor)
        ->postJson('/test/project-time-logs', [
            'project_id' => $project->id,
            'project_task_id' => $taskFromAnotherProject->id,
            'employee_id' => $otherEmployee->id,
            'log_date' => 'bad',
            'start_time' => '10:00',
            'end_time' => '09:00',
            'total_minutes' => -1,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['project_task_id', 'employee_id', 'log_date', 'end_time', 'total_minutes']);

    $this->actingAs($actor)
        ->postJson('/test/project-time-logs', [
            'project_id' => $project->id,
            'project_task_id' => $task->id,
            'employee_id' => $employee->id,
            'log_date' => '2026-05-13',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'total_minutes' => 60,
        ])
        ->assertSuccessful();
});
