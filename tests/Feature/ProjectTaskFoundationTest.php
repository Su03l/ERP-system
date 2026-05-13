<?php

use App\Enums\ProjectPriority;
use App\Enums\ProjectTaskStatus;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates the project tasks schema', function () {
    expect(Schema::hasColumns('project_tasks', [
        'id',
        'company_id',
        'project_id',
        'assigned_employee_id',
        'parent_task_id',
        'task_code',
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'start_date',
        'due_date',
        'completed_at',
        'status',
        'priority',
        'estimated_hours',
        'actual_hours',
        'progress_percentage',
        'workflow_instance_id',
        'metadata',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

it('stores tenant scoped project tasks with project employee and parent relationships', function () {
    $company = Company::factory()->create();
    $project = Project::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $parentTask = ProjectTask::factory()->for($company)->create([
        'project_id' => $project->id,
        'task_code' => 'TSK-PARENT',
    ]);

    $task = ProjectTask::factory()->for($company)->create([
        'project_id' => $project->id,
        'assigned_employee_id' => $employee->id,
        'parent_task_id' => $parentTask->id,
        'task_code' => 'TSK-001',
        'title_ar' => 'مهمة تجريبية',
        'status' => ProjectTaskStatus::InProgress,
        'priority' => ProjectPriority::High,
        'estimated_hours' => '12.50',
        'actual_hours' => '4.25',
        'metadata' => ['sprint' => 'one'],
    ]);

    expect($task->company->is($company))->toBeTrue()
        ->and($company->projectTasks()->whereKey($task)->exists())->toBeTrue()
        ->and($project->tasks()->whereKey($task)->exists())->toBeTrue()
        ->and($task->assignedEmployee->is($employee))->toBeTrue()
        ->and($employee->assignedProjectTasks()->whereKey($task)->exists())->toBeTrue()
        ->and($task->parentTask->is($parentTask))->toBeTrue()
        ->and($parentTask->childTasks()->whereKey($task)->exists())->toBeTrue()
        ->and($task->status)->toBe(ProjectTaskStatus::InProgress)
        ->and($task->priority)->toBe(ProjectPriority::High)
        ->and($task->estimated_hours)->toBe('12.50')
        ->and($task->actual_hours)->toBe('4.25')
        ->and($task->metadata)->toBe(['sprint' => 'one']);
});

it('scopes project tasks to the current company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $project = Project::factory()->for($company)->create();
    $otherProject = Project::factory()->for($otherCompany)->create();
    $task = ProjectTask::factory()->for($company)->create(['project_id' => $project->id]);
    ProjectTask::factory()->for($otherCompany)->create(['project_id' => $otherProject->id]);

    $this->actingAs($user);

    expect(ProjectTask::query()->forCurrentCompany()->pluck('id')->all())->toBe([$task->id]);
});
