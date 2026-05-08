<?php

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowAction;
use App\Models\WorkflowStep;
use App\Services\WorkflowExecutionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('workflow execution can start a generic approval instance', function () {
    $company = Company::factory()->create();
    $requester = User::factory()->for($company)->create();
    $role = Role::factory()->for($company)->create();
    $workflow = Workflow::factory()->for($company)->create(['module_key' => 'leave']);
    $step = WorkflowStep::factory()->for($workflow)->create([
        'approver_type' => 'role',
        'approver_value' => (string) $role->id,
        'order' => 1,
    ]);

    $this->actingAs($requester);

    $instance = app(WorkflowExecutionService::class)->start($workflow, $requester, payload: ['request_id' => 123]);

    expect($instance->company_id)->toBe($company->id)
        ->and($instance->workflow_id)->toBe($workflow->id)
        ->and($instance->current_step_id)->toBe($step->id)
        ->and($instance->status)->toBe('pending')
        ->and($instance->payload)->toBe(['request_id' => 123])
        ->and(AuditLog::where('action', 'workflow.instance.started')->exists())->toBeTrue();
});

test('approving a step advances to the next step and then completes', function () {
    [$company, $approver, $workflow, $firstStep, $secondStep] = workflowFixture();

    $this->actingAs($approver);

    $instance = app(WorkflowExecutionService::class)->start($workflow, $approver);
    $instance = app(WorkflowExecutionService::class)->approve($instance, $approver, 'Looks good');

    expect($instance->status)->toBe('pending')
        ->and($instance->current_step_id)->toBe($secondStep->id);

    $instance = app(WorkflowExecutionService::class)->approve($instance, $approver);

    expect($instance->status)->toBe('completed')
        ->and($instance->current_step_id)->toBeNull()
        ->and($instance->completed_at)->not->toBeNull()
        ->and(WorkflowAction::where('action', 'approved')->count())->toBe(2)
        ->and(AuditLog::where('action', 'workflow.instance.approved')->count())->toBe(2);
});

test('rejecting a step marks the instance rejected', function () {
    [$company, $approver, $workflow] = workflowFixture();

    $this->actingAs($approver);

    $instance = app(WorkflowExecutionService::class)->start($workflow, $approver);
    $instance = app(WorkflowExecutionService::class)->reject($instance, $approver, 'No');

    expect($instance->status)->toBe('rejected')
        ->and($instance->completed_at)->not->toBeNull()
        ->and(WorkflowAction::where('action', 'rejected')->exists())->toBeTrue();
});

test('returning moves back to previous step when practical', function () {
    [$company, $approver, $workflow, $firstStep, $secondStep] = workflowFixture();

    $this->actingAs($approver);

    $instance = app(WorkflowExecutionService::class)->start($workflow, $approver);
    $instance = app(WorkflowExecutionService::class)->approve($instance, $approver);
    $instance = app(WorkflowExecutionService::class)->returnBack($instance, $approver, 'Need edits');

    expect($instance->status)->toBe('pending')
        ->and($instance->current_step_id)->toBe($firstStep->id)
        ->and(WorkflowAction::where('action', 'returned')->exists())->toBeTrue();
});

test('unassigned users cannot approve workflow steps', function () {
    [$company, $approver, $workflow] = workflowFixture();
    $otherUser = User::factory()->for($company)->create();

    $this->actingAs($approver);
    $instance = app(WorkflowExecutionService::class)->start($workflow, $approver);

    app(WorkflowExecutionService::class)->approve($instance, $otherUser);
})->throws(AuthorizationException::class);

/**
 * @return array{Company, User, Workflow, WorkflowStep, WorkflowStep}
 */
function workflowFixture(): array
{
    $company = Company::factory()->create();
    $approver = User::factory()->for($company)->create();
    $role = Role::factory()->for($company)->create();
    $approver->roles()->attach($role, ['company_id' => $company->id]);
    $workflow = Workflow::factory()->for($company)->create();
    $firstStep = WorkflowStep::factory()->for($workflow)->create([
        'approver_type' => 'role',
        'approver_value' => (string) $role->id,
        'order' => 1,
    ]);
    $secondStep = WorkflowStep::factory()->for($workflow)->create([
        'approver_type' => 'role',
        'approver_value' => (string) $role->id,
        'order' => 2,
    ]);

    return [$company, $approver, $workflow, $firstStep, $secondStep];
}
