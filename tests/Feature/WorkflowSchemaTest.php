<?php

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowAction;
use App\Models\WorkflowInstance;
use App\Models\WorkflowStep;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('company specific workflows can store ordered approval steps', function () {
    $company = Company::factory()->create();
    $role = Role::factory()->for($company)->create();
    $workflow = Workflow::factory()->for($company)->create([
        'name' => 'Leave Approval',
        'module_key' => 'leave',
        'trigger_type' => 'submitted',
        'conditions' => ['leave_type' => 'annual'],
    ]);

    $firstStep = WorkflowStep::factory()->for($workflow)->create([
        'name' => 'Manager Approval',
        'approver_type' => 'role',
        'approver_value' => (string) $role->id,
        'order' => 1,
    ]);
    $secondStep = WorkflowStep::factory()->for($workflow)->create([
        'name' => 'HR Approval',
        'approver_type' => 'permission',
        'approver_value' => 'leave.approve',
        'order' => 2,
    ]);

    expect($workflow->steps)->toHaveCount(2)
        ->and($workflow->steps->first()->is($firstStep))->toBeTrue()
        ->and($workflow->steps->last()->is($secondStep))->toBeTrue()
        ->and(Workflow::forCompany($company)->first()->is($workflow))->toBeTrue();
});

test('workflow instances can store actions for approvals', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $workflow = Workflow::factory()->for($company)->create();
    $step = WorkflowStep::factory()->for($workflow)->create(['order' => 1]);
    $instance = WorkflowInstance::factory()->for($company)->for($workflow)->create([
        'current_step_id' => $step->id,
        'requested_by_id' => $user->id,
        'status' => 'pending',
        'payload' => ['amount' => 1000],
    ]);
    $action = WorkflowAction::factory()
        ->for($company)
        ->for($instance, 'workflowInstance')
        ->for($step, 'workflowStep')
        ->create([
            'acted_by_id' => $user->id,
            'action' => 'approved',
            'metadata' => ['source' => 'test'],
        ]);

    expect($instance->workflow->is($workflow))->toBeTrue()
        ->and($instance->currentStep->is($step))->toBeTrue()
        ->and($instance->actions)->toHaveCount(1)
        ->and($instance->actions->first()->is($action))->toBeTrue()
        ->and($action->actedBy->is($user))->toBeTrue();
});
