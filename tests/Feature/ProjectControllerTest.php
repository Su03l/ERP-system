<?php

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\ProjectTaskStatus;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTimeLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantProjectControllerPermissions(User $user, array $permissions): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissions as $permissionKey) {
        $permission = Permission::factory()->create(['key' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('lists projects with tenant scope and filters', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    grantProjectControllerPermissions($actor, ['projects.view']);
    $matching = Project::factory()->for($company)->create([
        'project_manager_id' => $employee->id,
        'status' => ProjectStatus::Active,
        'priority' => ProjectPriority::High,
        'progress_percentage' => 40,
        'name_ar' => 'مشروع مطابق',
        'start_date' => '2026-05-01',
    ]);
    ProjectTask::factory()->for($company)->create(['project_id' => $matching->id, 'assigned_employee_id' => $employee->id]);
    Project::factory()->for($company)->create(['status' => ProjectStatus::Draft]);
    Project::factory()->for(Company::factory())->create(['status' => ProjectStatus::Active]);

    $this->actingAs($actor)
        ->getJson(route('projects.index', [
            'status' => ProjectStatus::Active->value,
            'priority' => ProjectPriority::High->value,
            'assigned_employee_id' => $employee->id,
            'search' => 'مطابق',
            'progress_min' => 10,
            'progress_max' => 80,
        ]))
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $matching->id)
        ->assertJsonCount(1, 'data');
});

it('creates updates shows and archives projects through endpoints', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantProjectControllerPermissions($actor, ['projects.view', 'projects.create', 'projects.update', 'projects.delete']);

    $projectId = $this->actingAs($actor)
        ->postJson(route('projects.store'), [
            'code' => 'PRJ-HTTP',
            'name_ar' => 'مشروع',
            'status' => ProjectStatus::Draft->value,
            'priority' => ProjectPriority::Medium->value,
        ])
        ->assertSuccessful()
        ->json('data.id');

    $this->actingAs($actor)->patchJson(route('projects.update', $projectId), [
        'status' => ProjectStatus::Active->value,
    ])->assertSuccessful()->assertJsonPath('data.status', ProjectStatus::Active->value);

    $this->actingAs($actor)->getJson(route('projects.show', $projectId))->assertSuccessful();
    $this->actingAs($actor)->deleteJson(route('projects.destroy', $projectId))->assertNoContent();

    expect(Project::withTrashed()->find($projectId)->trashed())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'project.archived')->where('auditable_id', $projectId)->exists())->toBeTrue();
});

it('manages project tasks and completion through endpoints', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $project = Project::factory()->for($company)->create();
    grantProjectControllerPermissions($actor, ['project_tasks.view', 'project_tasks.create', 'project_tasks.update', 'project_tasks.complete', 'project_tasks.delete']);

    $taskId = $this->actingAs($actor)
        ->postJson(route('project-tasks.store'), [
            'project_id' => $project->id,
            'title_ar' => 'مهمة',
            'status' => ProjectTaskStatus::Todo->value,
            'priority' => ProjectPriority::Medium->value,
            'progress_percentage' => 10,
        ])
        ->assertSuccessful()
        ->json('data.id');

    $this->actingAs($actor)->getJson(route('project-tasks.index', [
        'project_id' => $project->id,
        'status' => ProjectTaskStatus::Todo->value,
    ]))->assertSuccessful()->assertJsonPath('data.0.id', $taskId);

    $this->actingAs($actor)->postJson(route('project-tasks.complete', $taskId))
        ->assertSuccessful()
        ->assertJsonPath('data.status', ProjectTaskStatus::Completed->value);

    $this->actingAs($actor)->deleteJson(route('project-tasks.destroy', $taskId))->assertNoContent();

    expect(ProjectTask::query()->whereKey($taskId)->exists())->toBeFalse();
});

it('manages project time logs through endpoints', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $project = Project::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    grantProjectControllerPermissions($actor, ['project_time_logs.view', 'project_time_logs.create', 'project_time_logs.update', 'project_time_logs.delete']);

    $timeLogId = $this->actingAs($actor)
        ->postJson(route('project-time-logs.store'), [
            'project_id' => $project->id,
            'employee_id' => $employee->id,
            'log_date' => '2026-05-14',
            'start_time' => '09:00',
            'end_time' => '10:30',
            'total_minutes' => 1,
            'is_billable' => true,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.total_minutes', 90)
        ->json('data.id');

    $this->actingAs($actor)->getJson(route('project-time-logs.index', [
        'project_id' => $project->id,
        'is_billable' => true,
        'logged_from' => '2026-05-14',
    ]))->assertSuccessful()->assertJsonPath('data.0.id', $timeLogId);

    $this->actingAs($actor)->patchJson(route('project-time-logs.update', $timeLogId), [
        'end_time' => '11:00',
    ])->assertSuccessful()->assertJsonPath('data.total_minutes', 120);

    $this->actingAs($actor)->deleteJson(route('project-time-logs.destroy', $timeLogId))->assertNoContent();

    expect(ProjectTimeLog::query()->whereKey($timeLogId)->exists())->toBeFalse();
});

it('prevents project routes from exposing other company records', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantProjectControllerPermissions($actor, ['projects.view', 'project_tasks.view', 'project_time_logs.view']);
    $otherCompany = Company::factory()->create();
    $otherProject = Project::factory()->for($otherCompany)->create();
    $otherTask = ProjectTask::factory()->for($otherCompany)->create(['project_id' => $otherProject->id]);
    $otherLog = ProjectTimeLog::factory()->for($otherCompany)->create(['project_id' => $otherProject->id]);

    $this->actingAs($actor)->getJson(route('projects.show', $otherProject))->assertForbidden();
    $this->actingAs($actor)->getJson(route('project-tasks.show', $otherTask))->assertForbidden();
    $this->actingAs($actor)->getJson(route('project-time-logs.show', $otherLog))->assertForbidden();
});
