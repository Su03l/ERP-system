<?php

use App\Actions\ArchiveProject;
use App\Actions\CompleteProjectTask;
use App\Actions\CreateProject;
use App\Actions\CreateProjectTask;
use App\Actions\UpdateProject;
use App\Actions\UpdateProjectTask;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\ProjectTaskStatus;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function grantProjectActionPermissions(User $user, array $permissions): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissions as $permissionKey) {
        $permission = Permission::factory()->create(['key' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('creates updates and archives projects with tenant ownership and audit logging', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $manager = Employee::factory()->for($company)->create();
    grantProjectActionPermissions($actor, ['projects.create', 'projects.update', 'projects.delete']);
    $this->actingAs($actor);

    $project = app(CreateProject::class)->handle([
        'company_id' => Company::factory()->create()->id,
        'project_manager_id' => $manager->id,
        'code' => 'PRJ-ACT',
        'name_ar' => 'مشروع',
        'status' => ProjectStatus::Draft,
        'priority' => ProjectPriority::Medium,
    ]);

    app(UpdateProject::class)->handle($project, ['status' => ProjectStatus::Active, 'progress_percentage' => 25]);
    app(ArchiveProject::class)->handle($project);

    expect($project->refresh()->company_id)->toBe($company->id)
        ->and($project->status)->toBe(ProjectStatus::Active)
        ->and($project->trashed())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'project.created')->where('auditable_id', $project->id)->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'project.updated')->where('auditable_id', $project->id)->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'project.archived')->where('auditable_id', $project->id)->exists())->toBeTrue();
});

it('creates updates completes tasks and recalculates project progress', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $project = Project::factory()->for($company)->create(['progress_percentage' => 0]);
    grantProjectActionPermissions($actor, ['project_tasks.create', 'project_tasks.update', 'project_tasks.complete']);
    $this->actingAs($actor);

    $firstTask = app(CreateProjectTask::class)->handle([
        'project_id' => $project->id,
        'assigned_employee_id' => $employee->id,
        'title_ar' => 'مهمة 1',
        'status' => ProjectTaskStatus::Todo,
        'priority' => ProjectPriority::Medium,
        'progress_percentage' => 50,
    ]);

    app(CreateProjectTask::class)->handle([
        'project_id' => $project->id,
        'title_ar' => 'مهمة 2',
        'status' => ProjectTaskStatus::Todo,
        'priority' => ProjectPriority::Medium,
        'progress_percentage' => 0,
    ]);

    app(UpdateProjectTask::class)->handle($firstTask, ['progress_percentage' => 60]);
    app(CompleteProjectTask::class)->handle($firstTask);

    expect($firstTask->refresh()->status)->toBe(ProjectTaskStatus::Completed)
        ->and($firstTask->progress_percentage)->toBe(100)
        ->and($project->refresh()->progress_percentage)->toBe(50)
        ->and(AuditLog::query()->where('action', 'project_task.completed')->where('auditable_id', $firstTask->id)->exists())->toBeTrue();
});

it('rejects cross-company project task relations in actions', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $project = Project::factory()->for($company)->create();
    $otherEmployee = Employee::factory()->for(Company::factory())->create();
    grantProjectActionPermissions($actor, ['project_tasks.create']);
    $this->actingAs($actor);

    app(CreateProjectTask::class)->handle([
        'project_id' => $project->id,
        'assigned_employee_id' => $otherEmployee->id,
        'title_ar' => 'مهمة',
        'status' => ProjectTaskStatus::Todo,
        'priority' => ProjectPriority::Medium,
    ]);
})->throws(ValidationException::class);

it('requires project permissions for actions', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $this->actingAs($actor);

    app(CreateProject::class)->handle([
        'code' => 'PRJ-DENIED',
        'name_ar' => 'مشروع',
        'status' => ProjectStatus::Draft,
        'priority' => ProjectPriority::Medium,
    ]);
})->throws(AuthorizationException::class);
