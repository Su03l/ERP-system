<?php

use App\Enums\PayrollCycleType;
use App\Enums\SalaryCalculationType;
use App\Enums\SalaryComponentStatus;
use App\Enums\SalaryComponentType;
use App\Enums\SalaryPackageStatus;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeSalaryPackage;
use App\Models\EmployeeSalaryPackageItem;
use App\Models\PayrollSetting;
use App\Models\SalaryComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores tenant scoped payroll settings with Arabic payslips by default', function () {
    $company = Company::factory()->create();

    $setting = PayrollSetting::factory()->create([
        'company_id' => $company->id,
    ]);

    expect($setting->company->is($company))->toBeTrue()
        ->and($company->payrollSetting->is($setting))->toBeTrue()
        ->and($setting->payroll_cycle_type)->toBe(PayrollCycleType::Monthly)
        ->and($setting->default_pay_day)->toBe(1)
        ->and($setting->payslip_language)->toBe('ar')
        ->and($setting->default_currency)->toBe('SAR')
        ->and($setting->overtime_calculation_enabled)->toBeTrue()
        ->and($setting->absence_deduction_enabled)->toBeTrue()
        ->and($setting->late_deduction_enabled)->toBeTrue()
        ->and($setting->payroll_approval_required)->toBeTrue();
});

it('stores salary components per company with enum backed fields', function () {
    $component = SalaryComponent::factory()->create([
        'type' => SalaryComponentType::Allowance,
        'calculation_type' => SalaryCalculationType::Percentage,
        'default_amount' => null,
        'default_percentage' => 15,
        'status' => SalaryComponentStatus::Active,
    ]);

    expect($component->company->salaryComponents()->whereKey($component)->exists())->toBeTrue()
        ->and($component->type)->toBe(SalaryComponentType::Allowance)
        ->and($component->calculation_type)->toBe(SalaryCalculationType::Percentage)
        ->and($component->status)->toBe(SalaryComponentStatus::Active)
        ->and($component->default_percentage)->toBe('15.00');
});

it('stores employee salary packages and dynamic component items in one company', function () {
    $company = Company::factory()->create();
    $employee = Employee::factory()->create(['company_id' => $company->id]);
    $component = SalaryComponent::factory()->create(['company_id' => $company->id]);

    $package = EmployeeSalaryPackage::factory()->create([
        'company_id' => $company->id,
        'employee_id' => $employee->id,
        'basic_salary' => 12000,
        'status' => SalaryPackageStatus::Active,
    ]);

    $item = EmployeeSalaryPackageItem::factory()->create([
        'company_id' => $company->id,
        'employee_salary_package_id' => $package->id,
        'salary_component_id' => $component->id,
        'amount' => 1000,
    ]);

    expect($company->employeeSalaryPackages()->whereKey($package)->exists())->toBeTrue()
        ->and($employee->salaryPackages()->whereKey($package)->exists())->toBeTrue()
        ->and($package->employee->is($employee))->toBeTrue()
        ->and($package->items()->whereKey($item)->exists())->toBeTrue()
        ->and($item->salaryPackage->is($package))->toBeTrue()
        ->and($item->salaryComponent->is($component))->toBeTrue()
        ->and($package->basic_salary)->toBe('12000.00')
        ->and($package->status)->toBe(SalaryPackageStatus::Active);
});

it('provides localized payroll enum labels', function () {
    app()->setLocale('ar');

    expect(PayrollCycleType::Monthly->label())->toBe('شهري')
        ->and(SalaryComponentType::Deduction->label())->toBe('استقطاع')
        ->and(SalaryCalculationType::Fixed->label())->toBe('مبلغ ثابت')
        ->and(SalaryComponentStatus::Inactive->label())->toBe('غير نشط')
        ->and(SalaryPackageStatus::Active->label())->toBe('نشط');
});
