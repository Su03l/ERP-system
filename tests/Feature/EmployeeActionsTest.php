<?php

use App\Actions\ArchiveEmployee;
use App\Actions\AssignEmployeeToUser;
use App\Actions\CreateEmployee;
use App\Actions\UpdateEmployee;
use App\Enums\EmployeeStatus;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function employeeActionPayload(array $overrides = []): array
{
    return array_merge([
        'employee_number' => 'EMP-501',
        'first_name_ar' => 'ليان',
        'last_name_ar' => 'سالم',
        'employment_status' => EmployeeStatus::Active->value,
    ], $overrides);
}

test('create employee action attaches current company and audits creation', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();

    $this->actingAs($actor);

    $employee = app(CreateEmployee::class)->handle(employeeActionPayload(), $actor);

    expect($employee->company_id)->toBe($company->id)
        ->and($employee->employee_number)->toBe('EMP-501')
        ->and(AuditLog::query()->where('action', 'employee.created')->where('auditable_id', $employee->id)->exists())->toBeTrue();
});

test('update employee action respects current company and audits changes', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create(['first_name_ar' => 'قديم']);

    $this->actingAs($actor);

    $updatedEmployee = app(UpdateEmployee::class)->handle($employee, ['first_name_ar' => 'جديد'], $actor);

    expect($updatedEmployee->first_name_ar)->toBe('جديد')
        ->and(AuditLog::query()->where('action', 'employee.updated')->where('auditable_id', $employee->id)->exists())->toBeTrue();
});

test('archive employee action soft deletes and audits employee', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();

    $this->actingAs($actor);

    app(ArchiveEmployee::class)->handle($employee, $actor);

    expect($employee->trashed())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'employee.archived')->where('auditable_id', $employee->id)->exists())->toBeTrue();
});

test('assign employee to user action requires same current company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $user = User::factory()->for($company)->create();
    $otherUser = User::factory()->for($otherCompany)->create();

    $this->actingAs($actor);

    $assignedEmployee = app(AssignEmployeeToUser::class)->handle($employee, $user, $actor);

    expect($assignedEmployee->user_id)->toBe($user->id);

    app(AssignEmployeeToUser::class)->handle($employee, $otherUser, $actor);
})->throws(AuthorizationException::class);

test('employee actions reject employees outside current company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($otherCompany)->create();

    $this->actingAs($actor);

    app(UpdateEmployee::class)->handle($employee, ['first_name_ar' => 'محاولة'], $actor);
})->throws(AuthorizationException::class);
