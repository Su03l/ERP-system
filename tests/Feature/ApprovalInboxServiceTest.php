<?php

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowInstance;
use App\Models\WorkflowStep;
use App\Services\ApprovalInboxService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('approval inbox returns pending role assigned approvals for current user', function () {
    [$company, $user, $role] = inboxUserFixture();
    $workflow = Workflow::factory()->for($company)->create(['module_key' => 'leave']);
    $step = WorkflowStep::factory()->for($workflow)->create([
        'approver_type' => 'role',
        'approver_value' => (string) $role->id,
        'order' => 1,
    ]);
    $instance = WorkflowInstance::factory()->for($company)->for($workflow)->create([
        'current_step_id' => $step->id,
        'status' => 'pending',
    ]);

    $this->actingAs($user);

    $approvals = app(ApprovalInboxService::class)->pendingFor($user);

    expect($approvals)->toHaveCount(1)
        ->and($approvals->first()->is($instance))->toBeTrue();
});

test('approval inbox supports module and status filters', function () {
    [$company, $user, $role] = inboxUserFixture();
    $leaveWorkflow = Workflow::factory()->for($company)->create(['module_key' => 'leave']);
    $payrollWorkflow = Workflow::factory()->for($company)->create(['module_key' => 'payroll']);
    $leaveStep = WorkflowStep::factory()->for($leaveWorkflow)->create([
        'approver_type' => 'role',
        'approver_value' => (string) $role->id,
        'order' => 1,
    ]);
    $payrollStep = WorkflowStep::factory()->for($payrollWorkflow)->create([
        'approver_type' => 'role',
        'approver_value' => (string) $role->id,
        'order' => 1,
    ]);
    $leaveInstance = WorkflowInstance::factory()->for($company)->for($leaveWorkflow)->create([
        'current_step_id' => $leaveStep->id,
        'status' => 'pending',
    ]);
    WorkflowInstance::factory()->for($company)->for($payrollWorkflow)->create([
        'current_step_id' => $payrollStep->id,
        'status' => 'pending',
    ]);

    $this->actingAs($user);

    $approvals = app(ApprovalInboxService::class)->pendingFor($user, ['module_key' => 'leave']);

    expect($approvals)->toHaveCount(1)
        ->and($approvals->first()->is($leaveInstance))->toBeTrue();
});

test('approval inbox supports user and permission assignments', function () {
    [$company, $user] = inboxUserFixture();
    $workflow = Workflow::factory()->for($company)->create();
    $userStep = WorkflowStep::factory()->for($workflow)->create([
        'approver_type' => 'user',
        'approver_value' => (string) $user->id,
        'order' => 1,
    ]);
    $userInstance = WorkflowInstance::factory()->for($company)->for($workflow)->create([
        'current_step_id' => $userStep->id,
        'status' => 'pending',
    ]);

    $this->actingAs($user);

    expect(app(ApprovalInboxService::class)->pendingFor($user))->toHaveCount(1)
        ->and(app(ApprovalInboxService::class)->pendingFor($user)->first()->is($userInstance))->toBeTrue();
});

test('approval inbox is tenant scoped', function () {
    [$company, $user, $role] = inboxUserFixture();
    $otherCompany = Company::factory()->create();
    $otherWorkflow = Workflow::factory()->for($otherCompany)->create();
    $otherStep = WorkflowStep::factory()->for($otherWorkflow)->create([
        'approver_type' => 'role',
        'approver_value' => (string) $role->id,
        'order' => 1,
    ]);
    WorkflowInstance::factory()->for($otherCompany)->for($otherWorkflow)->create([
        'current_step_id' => $otherStep->id,
        'status' => 'pending',
    ]);

    $this->actingAs($user);

    expect(app(ApprovalInboxService::class)->pendingFor($user))->toHaveCount(0);
});

/**
 * @return array{Company, User, Role}
 */
function inboxUserFixture(): array
{
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $role = Role::factory()->for($company)->create();
    $user->roles()->attach($role, ['company_id' => $company->id]);

    return [$company, $user, $role];
}
