<?php

use App\Actions\ActivateSalaryPackage;
use App\Actions\CreateSalaryPackage;
use App\Actions\EndSalaryPackage;
use App\Actions\UpdateSalaryPackage;
use App\Enums\PayrollPeriodStatus;
use App\Enums\SalaryCalculationType;
use App\Enums\SalaryComponentStatus;
use App\Enums\SalaryComponentType;
use App\Enums\SalaryPackageStatus;
use App\Http\Requests\GeneratePayrollRunRequest;
use App\Http\Requests\StoreEmployeeSalaryPackageRequest;
use App\Http\Requests\StorePayrollPeriodRequest;
use App\Http\Requests\StoreSalaryComponentRequest;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeSalaryPackage;
use App\Models\PayrollPeriod;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SalaryComponent;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

// use for
function grantSalaryPackagePermissions(User $user, array $permissionKeys): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissionKeys as $permissionKey) {
        $permission = Permission::factory()->create(['key' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('validates salary components with tenant scoped code uniqueness', function () {
    Route::post('/test/salary-components', fn (StoreSalaryComponentRequest $request) => response()->json($request->validated()));

    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    SalaryComponent::factory()->for($company)->create(['code' => 'HOUSING']);

    $this->actingAs($actor)
        ->postJson('/test/salary-components', [
            'name_ar' => 'بدل سكن',
            'code' => 'HOUSING',
            'type' => SalaryComponentType::Allowance->value,
            'calculation_type' => SalaryCalculationType::Fixed->value,
            'default_amount' => 1000,
            'status' => SalaryComponentStatus::Active->value,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['code']);
});

it('rejects salary packages with employees or components outside the current company', function () {
    Route::post('/test/salary-packages', fn (StoreEmployeeSalaryPackageRequest $request) => response()->json($request->validated()));

    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $otherEmployee = Employee::factory()->for($otherCompany)->create();
    $otherComponent = SalaryComponent::factory()->for($otherCompany)->create();

    $this->actingAs($actor)
        ->postJson('/test/salary-packages', [
            'employee_id' => $otherEmployee->id,
            'basic_salary' => 12000,
            'effective_from' => '2026-01-01',
            'items' => [
                [
                    'salary_component_id' => $otherComponent->id,
                    'amount' => 1000,
                ],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['employee_id', 'items.0.salary_component_id']);
});

it('rejects overlapping payroll periods in form requests', function () {
    Route::post('/test/payroll-periods', fn (StorePayrollPeriodRequest $request) => response()->json($request->validated()));

    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();

    PayrollPeriod::factory()->for($company)->create([
        'starts_on' => '2026-02-01',
        'ends_on' => '2026-02-28',
    ]);

    $this->actingAs($actor)
        ->postJson('/test/payroll-periods', [
            'name_ar' => 'رواتب فبراير',
            'starts_on' => '2026-02-15',
            'ends_on' => '2026-03-14',
            'status' => PayrollPeriodStatus::Draft->value,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['starts_on']);
});

it('validates payroll run generation against current company period and employees', function () {
    Route::post('/test/payroll-runs/generate', fn (GeneratePayrollRunRequest $request) => response()->json($request->validated()));

    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $otherPeriod = PayrollPeriod::factory()->for($otherCompany)->create();
    $otherEmployee = Employee::factory()->for($otherCompany)->create();

    $this->actingAs($actor)
        ->postJson('/test/payroll-runs/generate', [
            'payroll_period_id' => $otherPeriod->id,
            'employee_ids' => [$otherEmployee->id],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['payroll_period_id', 'employee_ids.0']);
});

it('creates salary packages with items in the current company and audits the change', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $component = SalaryComponent::factory()->for($company)->create();
    grantSalaryPackagePermissions($actor, ['salary_packages.create']);

    $this->actingAs($actor);

    $salaryPackage = app(CreateSalaryPackage::class)->handle([
        'employee_id' => $employee->id,
        'basic_salary' => 15000,
        'housing_allowance' => 2000,
        'effective_from' => '2026-01-01',
        'status' => SalaryPackageStatus::Active->value,
        'items' => [
            [
                'salary_component_id' => $component->id,
                'amount' => 1000,
            ],
        ],
    ], $actor);

    expect($salaryPackage->company_id)->toBe($company->id)
        ->and($salaryPackage->items)->toHaveCount(1)
        ->and(AuditLog::query()->where('action', 'salary_package.created')->where('auditable_id', $salaryPackage->id)->exists())->toBeTrue();
});

it('updates activates and ends salary packages with audit logs', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $salaryPackage = EmployeeSalaryPackage::factory()->for($company)->create([
        'status' => SalaryPackageStatus::Inactive,
        'effective_from' => '2026-01-01',
        'effective_to' => '2026-12-31',
    ]);
    grantSalaryPackagePermissions($actor, ['salary_packages.update']);

    $this->actingAs($actor);

    $updated = app(UpdateSalaryPackage::class)->handle($salaryPackage, [
        'basic_salary' => 18000,
        'status' => SalaryPackageStatus::Inactive->value,
    ], $actor);

    $activated = app(ActivateSalaryPackage::class)->handle($updated, $actor);
    $activatedStatus = $activated->status;
    $ended = app(EndSalaryPackage::class)->handle($activated, '2026-06-30', $actor);

    expect($updated->basic_salary)->toBe('18000.00')
        ->and($activatedStatus)->toBe(SalaryPackageStatus::Active)
        ->and($ended->status)->toBe(SalaryPackageStatus::Inactive)
        ->and($ended->effective_to->toDateString())->toBe('2026-06-30')
        ->and(AuditLog::query()->where('action', 'salary_package.updated')->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'salary_package.activated')->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'salary_package.ended')->exists())->toBeTrue();
});

it('prevents conflicting active salary packages', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    grantSalaryPackagePermissions($actor, ['salary_packages.create']);

    EmployeeSalaryPackage::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'status' => SalaryPackageStatus::Active,
        'effective_from' => '2026-01-01',
        'effective_to' => null,
    ]);

    $this->actingAs($actor);

    app(CreateSalaryPackage::class)->handle([
        'employee_id' => $employee->id,
        'basic_salary' => 16000,
        'effective_from' => '2026-02-01',
        'status' => SalaryPackageStatus::Active->value,
    ], $actor);
})->throws(ValidationException::class);

it('requires salary permission for salary package actions', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();

    $this->actingAs($actor);

    app(CreateSalaryPackage::class)->handle([
        'employee_id' => $employee->id,
        'basic_salary' => 16000,
        'effective_from' => '2026-02-01',
    ], $actor);
})->throws(AuthorizationException::class);
