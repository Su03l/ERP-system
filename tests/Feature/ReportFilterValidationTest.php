<?php

use App\DTOs\ReportFilter;
use App\Http\Requests\ExecuteReportRequest;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

it('normalizes report filters with Arabic fallback locale', function () {
    $filter = ReportFilter::fromArray([
        'date_from' => '2026-05-01',
        'date_to' => '2026-05-31',
        'locale' => 'fr',
        'export_format' => 'csv',
        'status' => 'active',
    ], defaultCompanyId: 15);

    expect($filter->companyId)->toBe(15)
        ->and($filter->dateFrom?->toDateString())->toBe('2026-05-01')
        ->and($filter->dateTo?->toDateString())->toBe('2026-05-31')
        ->and($filter->locale)->toBe('ar')
        ->and($filter->normalizedFilters()['status'])->toBe('active');
});

it('validates report filters against current tenant ids', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $department = Department::factory()->for($company)->create();
    $otherEmployee = Employee::factory()->for($otherCompany)->create();

    $request = ExecuteReportRequest::create('/reports/execute', 'POST', [
        'report_key' => 'hr.employees',
        'department_id' => $department->id,
        'employee_id' => $otherEmployee->id,
        'company_id' => $company->id,
        'export_format' => 'csv',
        'locale' => 'ar',
    ]);
    $request->setUserResolver(fn () => $user);

    $validator = Validator::make($request->all(), $request->rules());

    foreach ($request->after() as $after) {
        $validator->after($after);
    }

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('employee_id'))->toBeTrue()
        ->and($validator->errors()->has('department_id'))->toBeFalse();
});

it('rejects cross-company report execution for tenant users', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();

    $request = ExecuteReportRequest::create('/reports/execute', 'POST', [
        'report_key' => 'hr.employees',
        'company_id' => $otherCompany->id,
        'export_format' => 'csv',
    ]);
    $request->setUserResolver(fn () => $user);

    $validator = Validator::make($request->all(), $request->rules());

    foreach ($request->after() as $after) {
        $validator->after($after);
    }

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('company_id'))->toBeTrue();
});
