<?php

use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('leave types are tenant owned and expose relationships and casts', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();

    $this->actingAs($user);

    $leaveType = LeaveType::factory()->for($company)->create([
        'name_ar' => 'إجازة سنوية',
        'name_en' => 'Annual Leave',
        'code' => 'ANNUAL',
        'default_days_per_year' => 21,
        'is_paid' => true,
        'requires_approval' => true,
        'allow_negative_balance' => false,
        'status' => 'active',
    ]);
    LeaveType::factory()->create(['name_ar' => 'خارج النطاق']);

    expect(LeaveType::query()->forCurrentCompany()->pluck('id')->all())->toBe([$leaveType->id])
        ->and($company->leaveTypes()->whereKey($leaveType->id)->exists())->toBeTrue()
        ->and($leaveType->company->is($company))->toBeTrue()
        ->and($leaveType->is_paid)->toBeTrue()
        ->and($leaveType->requires_approval)->toBeTrue()
        ->and($leaveType->allow_negative_balance)->toBeFalse()
        ->and($leaveType->default_days_per_year)->toBe('21.00');
});

test('leave balances connect employees and leave types inside the same company', function () {
    $company = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create();
    $leaveType = LeaveType::factory()->for($company)->create(['code' => 'SICK']);

    $leaveBalance = LeaveBalance::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'year' => 2026,
        'opening_balance' => 2,
        'accrued_days' => 15,
        'used_days' => 4,
        'remaining_days' => 13,
        'metadata' => ['source' => 'manual'],
    ]);

    expect($leaveBalance->employee->is($employee))->toBeTrue()
        ->and($leaveBalance->leaveType->is($leaveType))->toBeTrue()
        ->and($company->leaveBalances()->whereKey($leaveBalance->id)->exists())->toBeTrue()
        ->and($employee->leaveBalances()->whereKey($leaveBalance->id)->exists())->toBeTrue()
        ->and($leaveType->balances()->whereKey($leaveBalance->id)->exists())->toBeTrue()
        ->and($leaveBalance->year)->toBe(2026)
        ->and($leaveBalance->opening_balance)->toBe('2.00')
        ->and($leaveBalance->remaining_days)->toBe('13.00')
        ->and($leaveBalance->metadata)->toBe(['source' => 'manual']);
});

test('leave balance factory keeps employee and leave type in the same company', function () {
    $leaveBalance = LeaveBalance::factory()->create();

    expect($leaveBalance->employee->company_id)->toBe($leaveBalance->company_id)
        ->and($leaveBalance->leaveType->company_id)->toBe($leaveBalance->company_id);
});
