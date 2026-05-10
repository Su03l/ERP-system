<?php

use App\Enums\AttendanceSource;
use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Employee;
use App\Services\AttendanceImportDefinition;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('attendance import definition exposes attendance columns with arabic aliases', function () {
    $definition = app(AttendanceImportDefinition::class)->definition();

    expect($definition['entity_type'])->toBe('attendance_records')
        ->and($definition['module_key'])->toBe('attendance')
        ->and(collect($definition['columns'])->pluck('key')->all())->toContain('employee_number', 'attendance_date', 'clock_in_at', 'clock_out_at', 'status', 'source', 'notes')
        ->and($definition['columns'][0]['aliases'])->toContain('رقم الموظف');
});

test('attendance import rows validate and map employee by company employee number', function () {
    $company = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create(['employee_number' => 'EMP-100']);

    $result = app(AttendanceImportDefinition::class)->validateRow([
        'employee_number' => 'EMP-100',
        'attendance_date' => '2026-05-10',
        'clock_in_at' => '2026-05-10 09:00:00',
        'clock_out_at' => '2026-05-10 17:00:00',
        'status' => AttendanceStatus::Present->value,
        'source' => AttendanceSource::Import->value,
        'notes' => 'Imported row',
    ], $company);

    expect($result['valid'])->toBeTrue()
        ->and($result['data']['employee_id'])->toBe($employee->id)
        ->and($result['data'])->not->toHaveKey('company_id')
        ->and($result['data']['status'])->toBe(AttendanceStatus::Present->value);
});

test('attendance import validation rejects cross tenant employees invalid dates and duplicates', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create(['employee_number' => 'EMP-101']);
    Employee::factory()->for($otherCompany)->create(['employee_number' => 'EMP-OTHER']);
    AttendanceRecord::factory()->for($company)->for($employee)->create(['attendance_date' => '2026-05-10']);

    $duplicate = app(AttendanceImportDefinition::class)->validateRow([
        'employee_number' => 'EMP-101',
        'attendance_date' => '2026-05-10',
        'clock_in_at' => 'not-date',
        'clock_out_at' => '2026-05-10 17:00:00',
        'status' => 'invalid',
        'source' => 'bad-source',
    ], $company);

    $crossTenant = app(AttendanceImportDefinition::class)->validateRow([
        'employee_number' => 'EMP-OTHER',
        'attendance_date' => '2026-05-11',
        'status' => AttendanceStatus::Present->value,
    ], $company);

    expect($duplicate['valid'])->toBeFalse()
        ->and($duplicate['errors'])->toHaveKeys(['attendance_date', 'clock_in_at', 'status', 'source'])
        ->and($crossTenant['valid'])->toBeFalse()
        ->and($crossTenant['errors'])->toHaveKey('employee_number');
});

test('attendance import preview supports update mode for existing records', function () {
    $company = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create(['employee_number' => 'EMP-102']);
    AttendanceRecord::factory()->for($company)->for($employee)->create(['attendance_date' => '2026-05-10']);

    $preview = app(AttendanceImportDefinition::class)->preview([
        [
            'employee_number' => 'EMP-102',
            'attendance_date' => '2026-05-10',
            'status' => AttendanceStatus::Present->value,
        ],
    ], $company, 10, updateMode: true);

    expect($preview['entity_type'])->toBe('attendance_records')
        ->and($preview['total_rows'])->toBe(1)
        ->and($preview['preview_rows'][0]['valid'])->toBeTrue()
        ->and($preview['preview_rows'][0]['data']['employee_id'])->toBe($employee->id);
});
