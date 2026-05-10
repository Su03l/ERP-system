<?php

use App\Enums\AttendanceSource;
use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\AttendanceExportQuery;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantAttendanceExportPermissions(User $user, array $permissionKeys): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissionKeys as $permissionKey) {
        $permission = Permission::query()->firstOrCreate(
            ['key' => $permissionKey],
            [
                'name' => $permissionKey,
                'description' => null,
            ],
        );

        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

test('attendance export is scoped to current company and supports filters', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $department = Department::factory()->for($company)->create(['name_ar' => 'الموارد البشرية']);
    $jobTitle = JobTitle::factory()->for($company)->create(['name_ar' => 'محاسب']);
    $employee = Employee::factory()->for($company)->create([
        'department_id' => $department->id,
        'job_title_id' => $jobTitle->id,
        'employee_number' => 'EMP-800',
        'first_name_ar' => 'سارة',
        'last_name_ar' => 'أحمد',
        'first_name_en' => 'Sara',
        'last_name_en' => 'Ahmed',
    ]);
    $matchingRecord = AttendanceRecord::factory()->for($company)->for($employee)->create([
        'attendance_date' => '2026-05-10',
        'status' => AttendanceStatus::Late->value,
        'source' => AttendanceSource::Manual->value,
        'late_minutes' => 15,
        'overtime_minutes' => 30,
        'total_work_minutes' => 480,
        'notes' => 'Manual correction',
    ]);

    AttendanceRecord::factory()->for($company)->create(['attendance_date' => '2026-05-11', 'status' => AttendanceStatus::Present->value]);
    AttendanceRecord::factory()->for($otherCompany)->create(['attendance_date' => '2026-05-10', 'status' => AttendanceStatus::Late->value]);
    grantAttendanceExportPermissions($user, ['attendance.export']);

    $this->actingAs($user);

    $export = app(AttendanceExportQuery::class)->export([
        'employee_id' => $employee->id,
        'department_id' => $department->id,
        'status' => AttendanceStatus::Late->value,
        'date_from' => '2026-05-01',
        'date_to' => '2026-05-31',
    ], $user);

    expect($export['entity_type'])->toBe('attendance_records')
        ->and($export['module_key'])->toBe('attendance')
        ->and($export['rows'])->toHaveCount(1)
        ->and($export['rows'][0]['employee_number'])->toBe('EMP-800')
        ->and($export['rows'][0]['employee_name_ar'])->toBe('سارة أحمد')
        ->and($export['rows'][0]['employee_name_en'])->toBe('Sara Ahmed')
        ->and($export['rows'][0]['department'])->toBe('الموارد البشرية')
        ->and($export['rows'][0]['job_title'])->toBe('محاسب')
        ->and($export['rows'][0]['attendance_date'])->toBe('2026-05-10')
        ->and($export['rows'][0]['status'])->toBe(AttendanceStatus::Late->value)
        ->and($export['rows'][0]['status_label'])->toBe(AttendanceStatus::Late->label())
        ->and($export['rows'][0]['source_label'])->toBe(AttendanceSource::Manual->label())
        ->and($export['rows'][0]['late_minutes'])->toBe($matchingRecord->late_minutes)
        ->and(collect($export['columns'])->pluck('key')->all())->toContain('employee_number', 'attendance_date', 'status_label', 'total_work_minutes');
});

test('attendance export requires attendance export permission', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    AttendanceRecord::factory()->for($company)->create();

    $this->actingAs($user);

    app(AttendanceExportQuery::class)->export(actor: $user);
})->throws(AuthorizationException::class);
