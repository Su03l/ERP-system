<?php

use App\DTOs\KpiDateRange;
use App\Enums\AttendanceStatus;
use App\Enums\LeaveRequestStatus;
use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Services\Kpis\Attendance\AbsenceRateKpi;
use App\Services\Kpis\Attendance\AttendanceRateKpi;
use App\Services\Kpis\Attendance\LateRateKpi;
use App\Services\Kpis\Attendance\OvertimeTotalKpi;
use App\Services\Kpis\Leave\ApprovedLeaveDaysKpi;
use App\Services\Kpis\Leave\LeaveBalanceSummaryKpi;
use App\Services\Kpis\Leave\PendingLeaveRequestsKpi;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('resolves attendance KPI values for the company date range', function () {
    $company = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create();
    $range = KpiDateRange::fromDates('2026-01-01', '2026-01-31');

    AttendanceRecord::factory()->for($company)->for($employee)->create([
        'attendance_date' => '2026-01-05',
        'status' => AttendanceStatus::Present,
        'overtime_minutes' => 30,
    ]);
    AttendanceRecord::factory()->for($company)->for($employee)->create([
        'attendance_date' => '2026-01-06',
        'status' => AttendanceStatus::Late,
        'overtime_minutes' => 15,
    ]);
    AttendanceRecord::factory()->for($company)->for($employee)->create([
        'attendance_date' => '2026-01-07',
        'status' => AttendanceStatus::Absent,
    ]);

    expect(app(AttendanceRateKpi::class)->resolve($company, $range)->value)->toBe(66.67)
        ->and(app(AbsenceRateKpi::class)->resolve($company, $range)->value)->toBe(33.33)
        ->and(app(LateRateKpi::class)->resolve($company, $range)->value)->toBe(33.33)
        ->and(app(OvertimeTotalKpi::class)->resolve($company, $range)->value)->toBe(45);
});

it('resolves leave KPI values for the company date range', function () {
    $company = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create();
    $range = KpiDateRange::fromDates('2026-01-01', '2026-01-31');

    LeaveRequest::factory()->for($company)->for($employee)->create([
        'status' => LeaveRequestStatus::Pending,
        'start_date' => '2026-01-10',
        'end_date' => '2026-01-12',
        'total_days' => 3,
    ]);
    LeaveRequest::factory()->for($company)->for($employee)->create([
        'status' => LeaveRequestStatus::Approved,
        'start_date' => '2026-01-20',
        'end_date' => '2026-01-21',
        'total_days' => 2,
    ]);
    LeaveBalance::factory()->for($company)->for($employee)->create([
        'opening_balance' => 10,
        'accrued_days' => 5,
        'used_days' => 2,
        'remaining_days' => 13,
    ]);

    expect(app(PendingLeaveRequestsKpi::class)->resolve($company, $range)->value)->toBe(1)
        ->and(app(ApprovedLeaveDaysKpi::class)->resolve($company, $range)->value)->toBe(2.0)
        ->and(app(LeaveBalanceSummaryKpi::class)->resolve($company, $range)->value)->toBe(13.0);
});
