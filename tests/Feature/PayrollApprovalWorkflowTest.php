<?php

use App\Actions\ApprovePayrollRun;
use App\Actions\GeneratePayrollRun;
use App\Actions\RejectPayrollRun;
use App\Enums\EmployeeStatus;
use App\Enums\PayrollRunStatus;
use App\Enums\SalaryPackageStatus;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeSalaryPackage;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Models\PayrollSetting;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowAction;
use App\Models\WorkflowStep;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantPayrollApprovalPermissions(User $user, array $permissionKeys): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissionKeys as $permissionKey) {
        $permission = Permission::query()->firstOrCreate(
            ['key' => $permissionKey],
            ['name' => $permissionKey],
        );

        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

function payrollApprovalScenario(bool $approvalRequired = true): array
{
    $company = Company::factory()->create();
    PayrollSetting::factory()->for($company)->create([
        'payroll_approval_required' => $approvalRequired,
    ]);
    $period = PayrollPeriod::factory()->for($company)->create([
        'starts_on' => '2026-03-01',
        'ends_on' => '2026-03-31',
    ]);
    $employee = Employee::factory()->for($company)->create([
        'employment_status' => EmployeeStatus::Active,
    ]);
    EmployeeSalaryPackage::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'basic_salary' => 9000,
        'effective_from' => '2026-01-01',
        'effective_to' => null,
        'status' => SalaryPackageStatus::Active,
    ]);

    return [$company, $period, $employee];
}

it('starts payroll approval workflow when approval is required', function () {
    [$company, $period] = payrollApprovalScenario();
    $generator = User::factory()->for($company)->create();
    $approver = User::factory()->for($company)->create();
    grantPayrollApprovalPermissions($generator, ['payroll_runs.generate']);
    grantPayrollApprovalPermissions($approver, ['payroll_runs.approve']);

    $workflow = Workflow::factory()->for($company)->create([
        'module_key' => 'payroll',
        'trigger_type' => 'payroll_run_approval',
        'status' => 'active',
    ]);
    $step = WorkflowStep::factory()->for($workflow)->create([
        'approver_type' => 'permission',
        'approver_value' => 'payroll_runs.approve',
        'order' => 1,
    ]);

    $this->actingAs($generator);

    $run = app(GeneratePayrollRun::class)->handle($period, [
        'run_number' => 'PAY-MAR-2026',
    ], $generator);

    expect($run->status)->toBe(PayrollRunStatus::PendingApproval)
        ->and($run->workflow_instance_id)->not->toBeNull()
        ->and($run->workflowInstance->workflow_id)->toBe($workflow->id)
        ->and($run->workflowInstance->current_step_id)->toBe($step->id)
        ->and(AuditLog::query()->where('action', 'payroll_run.submitted_for_approval')->exists())->toBeTrue();

    $this->actingAs($approver);

    $approved = app(ApprovePayrollRun::class)->handle($run->refresh(), $approver, 'Approved for payment.');

    expect($approved->status)->toBe(PayrollRunStatus::Approved)
        ->and($approved->approved_by)->toBe($approver->id)
        ->and($approved->workflowInstance->refresh()->status)->toBe('completed')
        ->and(WorkflowAction::query()->where('workflow_instance_id', $approved->workflow_instance_id)->where('action', 'approved')->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'payroll_run.approved')->where('auditable_id', $approved->id)->exists())->toBeTrue();
});

it('keeps payroll run pending without workflow until an authorized approver approves it', function () {
    [$company, $period] = payrollApprovalScenario();
    $generator = User::factory()->for($company)->create();
    $approver = User::factory()->for($company)->create();
    grantPayrollApprovalPermissions($generator, ['payroll_runs.generate']);
    grantPayrollApprovalPermissions($approver, ['payroll_runs.approve']);

    $this->actingAs($generator);

    $run = app(GeneratePayrollRun::class)->handle($period, [], $generator);

    expect($run->status)->toBe(PayrollRunStatus::PendingApproval)
        ->and($run->workflow_instance_id)->toBeNull()
        ->and(AuditLog::query()->where('action', 'payroll_run.pending_approval_without_workflow')->exists())->toBeTrue();

    $this->actingAs($approver);

    $approved = app(ApprovePayrollRun::class)->handle($run->refresh(), $approver);

    expect($approved->status)->toBe(PayrollRunStatus::Approved)
        ->and($approved->approved_by)->toBe($approver->id);
});

it('rejects payroll runs through the approval action and audits the decision', function () {
    [$company] = payrollApprovalScenario();
    $rejector = User::factory()->for($company)->create();
    grantPayrollApprovalPermissions($rejector, ['payroll_runs.reject']);
    $run = PayrollRun::factory()->for($company)->create([
        'status' => PayrollRunStatus::PendingApproval,
    ]);

    $this->actingAs($rejector);

    $rejected = app(RejectPayrollRun::class)->handle($run, $rejector, 'Numbers need review.');

    expect($rejected->status)->toBe(PayrollRunStatus::Rejected)
        ->and(AuditLog::query()->where('action', 'payroll_run.rejected')->where('auditable_id', $run->id)->exists())->toBeTrue();
});

it('auto approves generated payroll when company approval is disabled', function () {
    [$company, $period] = payrollApprovalScenario(false);
    $generator = User::factory()->for($company)->create();
    grantPayrollApprovalPermissions($generator, ['payroll_runs.generate']);

    $this->actingAs($generator);

    $run = app(GeneratePayrollRun::class)->handle($period, [], $generator);

    expect($run->status)->toBe(PayrollRunStatus::Approved)
        ->and($run->approved_by)->toBe($generator->id)
        ->and(AuditLog::query()->where('action', 'payroll_run.approval_not_required')->where('auditable_id', $run->id)->exists())->toBeTrue();
});
