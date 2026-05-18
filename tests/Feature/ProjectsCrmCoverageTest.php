<?php

use App\Actions\CompleteProjectTask;
use App\Actions\CreateCrmContact;
use App\Actions\CreateCrmLead;
use App\Actions\CreateManualTimeLog;
use App\Actions\CreateProject;
use App\Actions\CreateProjectTask;
use App\Enums\ContactStatus;
use App\Enums\LeadStatus;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\ProjectTaskStatus;
use App\Models\Company;
use App\Models\CrmContact;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Project;
use App\Models\ProjectTimeLog;
use App\Models\Role;
use App\Models\User;
use App\Services\CalculateLoggedMinutes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function grantProjectsCrmCoveragePermissions216(User $user, array $permissionKeys): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissionKeys as $permissionKey) {
        $permission = Permission::query()->firstOrCreate(
            ['key' => $permissionKey],
            ['name' => $permissionKey, 'description' => null],
        );

        $role->permissions()->syncWithoutDetaching([$permission->id]);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

test('crm leads and contacts stay tenant scoped', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $assignedUser = User::factory()->for($company)->create();
    $otherCustomer = Customer::factory()->for($otherCompany)->create();
    grantProjectsCrmCoveragePermissions216($actor, [
        'crm_leads.create',
        'crm_contacts.create',
    ]);

    $this->actingAs($actor);

    $lead = app(CreateCrmLead::class)->handle([
        'assigned_user_id' => $assignedUser->id,
        'name_ar' => 'Lead',
        'status' => LeadStatus::New,
        'expected_value' => '1500.00',
    ], $actor);

    expect($lead->company_id)->toBe($company->id)
        ->and($lead->assigned_user_id)->toBe($assignedUser->id);

    app(CreateCrmContact::class)->handle([
        'customer_id' => $otherCustomer->id,
        'name_ar' => 'Contact',
        'status' => ContactStatus::Active,
    ], $actor);
})->throws(ValidationException::class);

test('project creation task hierarchy and assigned employees are tenant validated', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $customer = Customer::factory()->for($company)->create();
    $manager = Employee::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $otherEmployee = Employee::factory()->for(Company::factory())->create();
    grantProjectsCrmCoveragePermissions216($actor, [
        'projects.create',
        'project_tasks.create',
    ]);

    $this->actingAs($actor);

    $project = app(CreateProject::class)->handle([
        'customer_id' => $customer->id,
        'project_manager_id' => $manager->id,
        'code' => 'PRJ-216',
        'name_ar' => 'Project',
        'status' => ProjectStatus::Active,
        'priority' => ProjectPriority::Medium,
    ], $actor);
    $parentTask = app(CreateProjectTask::class)->handle([
        'project_id' => $project->id,
        'assigned_employee_id' => $employee->id,
        'title_ar' => 'Parent',
        'status' => ProjectTaskStatus::Todo,
        'priority' => ProjectPriority::Medium,
        'progress_percentage' => 25,
    ], $actor);

    expect($project->company_id)->toBe($company->id)
        ->and($project->customer_id)->toBe($customer->id)
        ->and($parentTask->parent_task_id)->toBeNull();

    app(CreateProjectTask::class)->handle([
        'project_id' => $project->id,
        'assigned_employee_id' => $otherEmployee->id,
        'parent_task_id' => $parentTask->id,
        'title_ar' => 'Invalid child',
        'status' => ProjectTaskStatus::Todo,
        'priority' => ProjectPriority::Medium,
    ], $actor);
})->throws(ValidationException::class);

test('time logs calculate duration and project progress recalculates on task completion', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $project = Project::factory()->for($company)->create(['progress_percentage' => 0]);
    grantProjectsCrmCoveragePermissions216($actor, [
        'project_tasks.create',
        'project_tasks.complete',
        'project_time_logs.create',
    ]);

    $this->actingAs($actor);

    $firstTask = app(CreateProjectTask::class)->handle([
        'project_id' => $project->id,
        'title_ar' => 'First',
        'status' => ProjectTaskStatus::Todo,
        'priority' => ProjectPriority::Medium,
        'progress_percentage' => 0,
    ], $actor);
    app(CreateProjectTask::class)->handle([
        'project_id' => $project->id,
        'title_ar' => 'Second',
        'status' => ProjectTaskStatus::Todo,
        'priority' => ProjectPriority::Medium,
        'progress_percentage' => 0,
    ], $actor);

    $timeLog = app(CreateManualTimeLog::class)->handle([
        'project_id' => $project->id,
        'project_task_id' => $firstTask->id,
        'employee_id' => $employee->id,
        'log_date' => '2026-05-13',
        'start_time' => '09:00',
        'end_time' => '10:30',
        'total_minutes' => 1,
        'is_billable' => true,
    ], $actor);
    app(CompleteProjectTask::class)->handle($firstTask, $actor);

    expect(app(CalculateLoggedMinutes::class)->handle('09:00', '10:30'))->toBe(90)
        ->and($timeLog->total_minutes)->toBe(90)
        ->and($project->refresh()->progress_percentage)->toBe(50);
});

test('crm and project permissions protect tenant resources', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantProjectsCrmCoveragePermissions216($actor, [
        'crm_contacts.view',
        'projects.view',
        'project_time_logs.view',
    ]);
    $contact = CrmContact::factory()->for($company)->create();
    $project = Project::factory()->for($company)->create();
    $timeLog = ProjectTimeLog::factory()->for($company)->create(['project_id' => $project->id]);
    $otherContact = CrmContact::factory()->for(Company::factory())->create();
    $otherProject = Project::factory()->for(Company::factory())->create();

    expect(Gate::forUser($actor)->allows('view', $contact))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('view', $project))->toBeTrue()
        ->and(Gate::forUser($actor)->allows('view', $timeLog))->toBeTrue()
        ->and(Gate::forUser($actor)->denies('view', $otherContact))->toBeTrue()
        ->and(Gate::forUser($actor)->denies('view', $otherProject))->toBeTrue();
});
