<?php

use App\Enums\EmployeeStatus;
use App\Enums\Gender;
use App\Enums\WorkType;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

function validEmployeePayload(array $overrides = []): array
{
    return array_merge([
        'employee_number' => 'EMP-100',
        'first_name_ar' => 'سارة',
        'last_name_ar' => 'أحمد',
        'first_name_en' => 'Sara',
        'last_name_en' => 'Ahmed',
        'email' => 'sara@example.com',
        'employment_status' => EmployeeStatus::Active->value,
        'gender' => Gender::Female->value,
        'work_type' => WorkType::FullTime->value,
        'basic_salary' => 12000,
    ], $overrides);
}

test('store employee request accepts valid tenant scoped input', function () {
    Route::post('/employee-validation-store', fn (StoreEmployeeRequest $request) => $request->validated());

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $department = Department::factory()->for($company)->create();
    $jobTitle = JobTitle::factory()->for($company)->create();
    $manager = Employee::factory()->for($company)->create();

    $this->actingAs($user)
        ->postJson('/employee-validation-store', validEmployeePayload([
            'department_id' => $department->id,
            'job_title_id' => $jobTitle->id,
            'manager_id' => $manager->id,
        ]))
        ->assertSuccessful()
        ->assertJsonPath('employee_number', 'EMP-100');
});

test('store employee request blocks cross company relationship ids', function () {
    Route::post('/employee-validation-cross-company', fn (StoreEmployeeRequest $request) => $request->validated());

    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $department = Department::factory()->for($otherCompany)->create();
    $jobTitle = JobTitle::factory()->for($otherCompany)->create();
    $manager = Employee::factory()->for($otherCompany)->create();

    $this->actingAs($user)
        ->postJson('/employee-validation-cross-company', validEmployeePayload([
            'department_id' => $department->id,
            'job_title_id' => $jobTitle->id,
            'manager_id' => $manager->id,
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['department_id', 'job_title_id', 'manager_id']);
});

test('store employee request enforces company scoped employee number uniqueness', function () {
    Route::post('/employee-validation-unique', fn (StoreEmployeeRequest $request) => $request->validated());

    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();

    Employee::factory()->for($company)->create(['employee_number' => 'EMP-100']);
    Employee::factory()->for($otherCompany)->create(['employee_number' => 'EMP-200']);

    $this->actingAs($user)
        ->postJson('/employee-validation-unique', validEmployeePayload())
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['employee_number']);

    $this->actingAs($user)
        ->postJson('/employee-validation-unique', validEmployeePayload(['employee_number' => 'EMP-200']))
        ->assertSuccessful();
});

test('store employee request requires arabic names and validates email and salary', function () {
    Route::post('/employee-validation-required', fn (StoreEmployeeRequest $request) => $request->validated());

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();

    $this->actingAs($user)
        ->postJson('/employee-validation-required', validEmployeePayload([
            'first_name_ar' => '',
            'last_name_ar' => '',
            'email' => 'invalid',
            'basic_salary' => 'not-number',
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['first_name_ar', 'last_name_ar', 'email', 'basic_salary']);
});

test('update employee request ignores current employee number and blocks self manager', function () {
    Route::patch('/employee-validation-update/{employee}', fn (UpdateEmployeeRequest $request, Employee $employee) => $request->validated());

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create(['employee_number' => 'EMP-100']);

    $this->actingAs($user)
        ->patchJson("/employee-validation-update/{$employee->id}", [
            'employee_number' => 'EMP-100',
            'manager_id' => $employee->id,
        ])
        ->assertUnprocessable()
        ->assertJsonMissingValidationErrors(['employee_number'])
        ->assertJsonValidationErrors(['manager_id']);
});
