<?php

use App\Models\Company;
use App\Models\Permission;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTimeLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

function grantProjectPolicyPermissions(User $user, array $permissions): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissions as $permissionKey) {
        $permission = Permission::factory()->create(['key' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('protects project permissions and tenant boundaries', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantProjectPolicyPermissions($actor, ['projects.view', 'projects.create', 'projects.update', 'projects.delete', 'projects.export']);
    $project = Project::factory()->for($company)->create();
    $otherProject = Project::factory()->for(Company::factory())->create();

    expect(Gate::forUser($actor)->allows('viewAny', Project::class))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('create', Project::class))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('view', $project))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('update', $project))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('delete', $project))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('export', Project::class))->toBeTrue()
        ->and(Gate::forUser($actor)->denies('view', $otherProject))->toBeTrue();
});

it('protects project task permissions and tenant boundaries', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantProjectPolicyPermissions($actor, ['project_tasks.view', 'project_tasks.create', 'project_tasks.update', 'project_tasks.complete', 'project_tasks.delete']);
    $task = ProjectTask::factory()->for($company)->create(['project_id' => Project::factory()->for($company)->create()->id]);
    $otherCompany = Company::factory()->create();
    $otherTask = ProjectTask::factory()->for($otherCompany)->create(['project_id' => Project::factory()->for($otherCompany)->create()->id]);

    expect(Gate::forUser($actor)->allows('viewAny', ProjectTask::class))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('create', ProjectTask::class))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('view', $task))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('update', $task))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('complete', $task))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('delete', $task))->toBeTrue()
        ->and(Gate::forUser($actor)->denies('view', $otherTask))->toBeTrue();
});

it('protects project time log permissions and tenant boundaries', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantProjectPolicyPermissions($actor, ['project_time_logs.view', 'project_time_logs.create', 'project_time_logs.update', 'project_time_logs.delete']);
    $timeLog = ProjectTimeLog::factory()->for($company)->create([
        'project_id' => Project::factory()->for($company)->create()->id,
    ]);
    $otherCompany = Company::factory()->create();
    $otherLog = ProjectTimeLog::factory()->for($otherCompany)->create([
        'project_id' => Project::factory()->for($otherCompany)->create()->id,
    ]);

    expect(Gate::forUser($actor)->allows('viewAny', ProjectTimeLog::class))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('create', ProjectTimeLog::class))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('view', $timeLog))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('update', $timeLog))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('delete', $timeLog))->toBeTrue()
        ->and(Gate::forUser($actor)->denies('view', $otherLog))->toBeTrue();
});

it('denies project access without permission grants', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $project = Project::factory()->for($company)->create();
    $task = ProjectTask::factory()->for($company)->create(['project_id' => $project->id]);
    $timeLog = ProjectTimeLog::factory()->for($company)->create(['project_id' => $project->id]);

    expect(Gate::forUser($actor)->denies('viewAny', Project::class))->toBeTrue()
        ->and(Gate::forUser($actor)->denies('update', $project))->toBeTrue()
        ->and(Gate::forUser($actor)->denies('viewAny', ProjectTask::class))->toBeTrue()
        ->and(Gate::forUser($actor)->denies('complete', $task))->toBeTrue()
        ->and(Gate::forUser($actor)->denies('viewAny', ProjectTimeLog::class))->toBeTrue()
        ->and(Gate::forUser($actor)->denies('update', $timeLog))->toBeTrue();
});
