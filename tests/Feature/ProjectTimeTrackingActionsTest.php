<?php

use App\Actions\CreateManualTimeLog;
use App\Actions\StartTimeLog;
use App\Actions\StopTimeLog;
use App\Actions\UpdateTimeLog;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Role;
use App\Models\User;
use App\Services\CalculateLoggedMinutes;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function grantProjectTimePermissions(User $user, array $permissions): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissions as $permissionKey) {
        $permission = Permission::factory()->create(['key' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('calculates logged minutes safely', function () {
    expect(app(CalculateLoggedMinutes::class)->handle('09:15', '10:45'))->toBe(90);

    app(CalculateLoggedMinutes::class)->handle('10:00', '09:00');
})->throws(ValidationException::class);

it('creates and updates manual time logs with calculated minutes and audit logging', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $project = Project::factory()->for($company)->create();
    $task = ProjectTask::factory()->for($company)->create(['project_id' => $project->id]);
    $employee = Employee::factory()->for($company)->create();
    grantProjectTimePermissions($actor, ['project_time_logs.create', 'project_time_logs.update']);
    $this->actingAs($actor);

    $timeLog = app(CreateManualTimeLog::class)->handle([
        'project_id' => $project->id,
        'project_task_id' => $task->id,
        'employee_id' => $employee->id,
        'log_date' => '2026-05-13',
        'start_time' => '09:00',
        'end_time' => '10:30',
        'total_minutes' => 1,
        'is_billable' => true,
    ]);

    app(UpdateTimeLog::class)->handle($timeLog, [
        'end_time' => '11:00',
    ]);

    expect($timeLog->refresh()->company_id)->toBe($company->id)
        ->and($timeLog->total_minutes)->toBe(120)
        ->and($timeLog->is_billable)->toBeTrue()
        ->and(AuditLog::query()->where('action', 'project_time_log.created')->where('auditable_id', $timeLog->id)->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'project_time_log.updated')->where('auditable_id', $timeLog->id)->exists())->toBeTrue();
});

it('starts and stops time logs', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $project = Project::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    grantProjectTimePermissions($actor, ['project_time_logs.create', 'project_time_logs.update']);
    $this->actingAs($actor);

    $timeLog = app(StartTimeLog::class)->handle([
        'project_id' => $project->id,
        'employee_id' => $employee->id,
    ], startedAt: CarbonImmutable::parse('2026-05-13 08:00:00'));

    app(StopTimeLog::class)->handle($timeLog, stoppedAt: CarbonImmutable::parse('2026-05-13 09:45:00'));

    expect($timeLog->refresh()->log_date->toDateString())->toBe('2026-05-13')
        ->and($timeLog->total_minutes)->toBe(105);
});

it('rejects cross-company time log relations in actions', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $project = Project::factory()->for($company)->create();
    $otherEmployee = Employee::factory()->for(Company::factory())->create();
    grantProjectTimePermissions($actor, ['project_time_logs.create']);
    $this->actingAs($actor);

    app(CreateManualTimeLog::class)->handle([
        'project_id' => $project->id,
        'employee_id' => $otherEmployee->id,
        'log_date' => '2026-05-13',
        'total_minutes' => 10,
    ]);
})->throws(ValidationException::class);

it('requires time log permissions for actions', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $project = Project::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $this->actingAs($actor);

    app(CreateManualTimeLog::class)->handle([
        'project_id' => $project->id,
        'employee_id' => $employee->id,
        'log_date' => '2026-05-13',
        'total_minutes' => 10,
    ]);
})->throws(AuthorizationException::class);
