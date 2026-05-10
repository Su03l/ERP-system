<?php

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveTypeStatus;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantLeavePermissions(User $user, array $permissionKeys): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissionKeys as $permissionKey) {
        $permission = Permission::query()->firstOrCreate(
            ['key' => $permissionKey],
            ['name' => $permissionKey, 'description' => null],
        );

        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

test('leave type routes are permission protected and audited', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantLeavePermissions($user, ['leave_types.view', 'leave_types.create', 'leave_types.update', 'leave_types.delete']);

    $response = $this->actingAs($user)
        ->postJson('/leave-types', [
            'name_ar' => 'إجازة سنوية',
            'code' => 'ANNUAL',
            'status' => LeaveTypeStatus::Active->value,
        ])
        ->assertSuccessful();

    $leaveTypeId = $response->json('data.id');

    $this->actingAs($user)
        ->patchJson("/leave-types/{$leaveTypeId}", ['name_ar' => 'إجازة محدثة'])
        ->assertSuccessful()
        ->assertJsonPath('data.name_ar', 'إجازة محدثة');

    $this->actingAs($user)
        ->deleteJson("/leave-types/{$leaveTypeId}")
        ->assertNoContent();

    expect(AuditLog::query()->where('action', 'leave_type.created')->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'leave_type.updated')->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'leave_type.deleted')->exists())->toBeTrue();
});

test('employee can create and submit own leave request while approval deducts balance', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create(['user_id' => $user->id]);
    $approver = User::factory()->for($company)->create();
    $leaveType = LeaveType::factory()->for($company)->create();
    LeaveBalance::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'year' => 2026,
        'remaining_days' => 10,
        'used_days' => 0,
    ]);
    grantLeavePermissions($approver, ['leave_requests.view', 'leave_requests.approve']);

    $response = $this->actingAs($user)
        ->postJson('/leave-requests', [
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-12',
            'reason' => 'Family',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.status', LeaveRequestStatus::Draft->value);

    $leaveRequestId = $response->json('data.id');

    $this->actingAs($user)
        ->postJson("/leave-requests/{$leaveRequestId}/submit")
        ->assertSuccessful()
        ->assertJsonPath('data.status', LeaveRequestStatus::Pending->value);

    $this->actingAs($approver)
        ->postJson("/leave-requests/{$leaveRequestId}/approve")
        ->assertSuccessful()
        ->assertJsonPath('data.status', LeaveRequestStatus::Approved->value);

    $balance = LeaveBalance::query()->where('employee_id', $employee->id)->where('leave_type_id', $leaveType->id)->first();

    expect($balance?->used_days)->toBe('3.00')
        ->and($balance?->remaining_days)->toBe('7.00')
        ->and(AuditLog::query()->where('action', 'leave_request.approved')->exists())->toBeTrue();
});

test('leave index routes are tenant scoped and filterable', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $leaveType = LeaveType::factory()->for($company)->create();
    $matchingRequest = LeaveRequest::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'status' => LeaveRequestStatus::Pending->value,
        'start_date' => '2026-05-10',
        'end_date' => '2026-05-12',
    ]);
    LeaveRequest::factory()->for($otherCompany)->create(['status' => LeaveRequestStatus::Pending->value]);
    grantLeavePermissions($user, ['leave_requests.view', 'leave_balances.view']);

    $this->actingAs($user)
        ->getJson("/leave-requests?employee_id={$employee->id}&leave_type_id={$leaveType->id}&status=pending&date_from=2026-05-01&date_to=2026-05-31")
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matchingRequest->id);
});
