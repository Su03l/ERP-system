<?php

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveTypeStatus;
use App\Http\Requests\StoreLeaveRequestRequest;
use App\Http\Requests\StoreLeaveTypeRequest;
use App\Http\Requests\UpdateLeaveRequestRequest;
use App\Http\Requests\UpdateLeaveTypeRequest;
use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

function leaveTypePayload(array $overrides = []): array
{
    return array_merge([
        'name_ar' => 'إجازة سنوية',
        'name_en' => 'Annual Leave',
        'code' => 'ANNUAL',
        'default_days_per_year' => 21,
        'is_paid' => true,
        'requires_approval' => true,
        'allow_negative_balance' => false,
        'status' => LeaveTypeStatus::Active->value,
    ], $overrides);
}

function leaveRequestPayload(Employee $employee, LeaveType $leaveType, array $overrides = []): array
{
    return array_merge([
        'employee_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'start_date' => '2026-05-10',
        'end_date' => '2026-05-12',
        'total_days' => 3,
        'reason' => 'Family trip',
        'status' => LeaveRequestStatus::Draft->value,
    ], $overrides);
}

test('leave type store request validates enum and tenant unique code', function () {
    Route::post('/leave-validation-types', fn (StoreLeaveTypeRequest $request) => $request->validated());

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    LeaveType::factory()->for($company)->create(['code' => 'ANNUAL']);

    $this->actingAs($user)
        ->postJson('/leave-validation-types', leaveTypePayload(['status' => 'bad']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['code', 'status']);
});

test('leave type update request ignores current type and blocks cross tenant access', function () {
    Route::patch('/leave-validation-types/{leave_type}', fn (UpdateLeaveTypeRequest $request, LeaveType $leaveType) => $request->validated());

    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $leaveType = LeaveType::factory()->for($company)->create(['code' => 'ANNUAL']);
    $otherLeaveType = LeaveType::factory()->for($otherCompany)->create(['code' => 'OTHER']);

    $this->actingAs($user)
        ->patchJson("/leave-validation-types/{$leaveType->id}", ['code' => 'ANNUAL'])
        ->assertSuccessful()
        ->assertJsonPath('code', 'ANNUAL');

    $this->actingAs($user)
        ->patchJson("/leave-validation-types/{$otherLeaveType->id}", ['name_ar' => 'محاولة'])
        ->assertForbidden();
});

test('leave request store validates tenant ownership dates enums and overlap', function () {
    Route::post('/leave-validation-requests', fn (StoreLeaveRequestRequest $request) => $request->validated());

    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $leaveType = LeaveType::factory()->for($company)->create();
    $otherEmployee = Employee::factory()->for($otherCompany)->create();
    $otherLeaveType = LeaveType::factory()->for($otherCompany)->create();
    LeaveRequest::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'start_date' => '2026-05-10',
        'end_date' => '2026-05-12',
        'status' => LeaveRequestStatus::Pending->value,
    ]);

    $this->actingAs($user)
        ->postJson('/leave-validation-requests', leaveRequestPayload($otherEmployee, $otherLeaveType, [
            'end_date' => '2026-05-01',
            'status' => 'bad',
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['employee_id', 'leave_type_id', 'end_date', 'status']);

    $this->actingAs($user)
        ->postJson('/leave-validation-requests', leaveRequestPayload($employee, $leaveType, [
            'start_date' => '2026-05-11',
            'end_date' => '2026-05-13',
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['start_date']);
});

test('leave request update allows current request date range but prevents new overlaps', function () {
    Route::patch('/leave-validation-requests/{leave_request}', fn (UpdateLeaveRequestRequest $request, LeaveRequest $leaveRequest) => $request->validated());

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $leaveType = LeaveType::factory()->for($company)->create();
    $leaveRequest = LeaveRequest::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'start_date' => '2026-05-10',
        'end_date' => '2026-05-12',
    ]);
    LeaveRequest::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'start_date' => '2026-05-20',
        'end_date' => '2026-05-22',
        'status' => LeaveRequestStatus::Pending->value,
    ]);

    $this->actingAs($user)
        ->patchJson("/leave-validation-requests/{$leaveRequest->id}", [
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-12',
        ])
        ->assertSuccessful();

    $this->actingAs($user)
        ->patchJson("/leave-validation-requests/{$leaveRequest->id}", [
            'start_date' => '2026-05-21',
            'end_date' => '2026-05-23',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['start_date']);
});
