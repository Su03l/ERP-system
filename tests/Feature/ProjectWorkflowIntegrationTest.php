<?php

use App\Actions\CompleteProjectTask;
use App\Actions\CreateProject;
use App\Actions\CreateProjectTask;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\ProjectTaskStatus;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Project;
use App\Models\ProjectCrmSetting;
use App\Models\Role;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowInstance;
use App\Models\WorkflowStep;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantProjectWorkflowPermissions(User $user, array $permissions): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissions as $permissionKey) {
        $permission = Permission::factory()->create(['key' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('starts project approval workflow when company settings require it', function () {
    $company = Company::factory()->create();
    ProjectCrmSetting::factory()->for($company)->create(['project_approval_required' => true]);
    $actor = User::factory()->for($company)->create();
    grantProjectWorkflowPermissions($actor, ['projects.create']);
    $workflow = projectWorkflow($company, 'project_approval');
    $this->actingAs($actor);

    $project = app(CreateProject::class)->handle([
        'code' => 'PRJ-WF',
        'name_ar' => 'مشروع موافقة',
        'status' => ProjectStatus::Draft,
        'priority' => ProjectPriority::Medium,
    ]);

    expect($project->refresh()->workflow_instance_id)->not->toBeNull()
        ->and($project->status)->toBe(ProjectStatus::PendingApproval)
        ->and(WorkflowInstance::query()->where('workflow_id', $workflow->id)->where('subject_id', $project->id)->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'workflow.instance.started')->exists())->toBeTrue();
});

it('starts task approval and task completion approval workflows when enabled', function () {
    $company = Company::factory()->create();
    ProjectCrmSetting::factory()->for($company)->create(['task_approval_required' => true]);
    $actor = User::factory()->for($company)->create();
    grantProjectWorkflowPermissions($actor, ['project_tasks.create', 'project_tasks.complete']);
    $project = Project::factory()->for($company)->create();
    projectWorkflow($company, 'task_approval');
    projectWorkflow($company, 'task_completion_approval');
    $this->actingAs($actor);

    $task = app(CreateProjectTask::class)->handle([
        'project_id' => $project->id,
        'title_ar' => 'مهمة موافقة',
        'status' => ProjectTaskStatus::Todo,
        'priority' => ProjectPriority::Medium,
    ]);

    expect($task->refresh()->workflow_instance_id)->not->toBeNull()
        ->and($task->status)->toBe(ProjectTaskStatus::PendingApproval);

    $task->forceFill(['workflow_instance_id' => null, 'status' => ProjectTaskStatus::Todo])->save();
    app(CompleteProjectTask::class)->handle($task);

    expect($task->refresh()->workflow_instance_id)->not->toBeNull()
        ->and($task->status)->toBe(ProjectTaskStatus::PendingApproval)
        ->and(AuditLog::query()->where('action', 'project_task.completion_approval_requested')->exists())->toBeTrue();
});

function projectWorkflow(Company $company, string $triggerType): Workflow
{
    $workflow = Workflow::factory()->for($company)->create([
        'module_key' => 'projects',
        'trigger_type' => $triggerType,
        'status' => 'active',
    ]);

    WorkflowStep::factory()->for($workflow)->create([
        'approver_type' => 'permission',
        'approver_value' => 'projects.approve',
        'order' => 1,
    ]);

    return $workflow;
}
