<?php

use App\Enums\PayrollRunItemStatus;
use App\Enums\SalaryCalculationType;
use App\Enums\SalaryComponentType;
use App\Enums\SalaryPackageStatus;
use App\Models\Company;
use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\PayrollRunItem;
use App\Models\PayrollRunItemComponent;
use App\Models\SalaryComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores tenant scoped payroll run items with sensitive salary totals', function () {
    $company = Company::factory()->create();
    $employee = Employee::factory()->create(['company_id' => $company->id]);
    $run = PayrollRun::factory()->create(['company_id' => $company->id]);

    $item = PayrollRunItem::factory()->create([
        'company_id' => $company->id,
        'payroll_run_id' => $run->id,
        'employee_id' => $employee->id,
        'basic_salary' => 10000,
        'gross_salary' => 12500,
        'total_allowances' => 2000,
        'total_deductions' => 500,
        'net_salary' => 12000,
        'attendance_deduction' => 200,
        'leave_deduction' => 100,
        'overtime_amount' => 500,
        'status' => PayrollRunItemStatus::Calculated,
    ]);

    expect($item->company->is($company))->toBeTrue()
        ->and($company->payrollRunItems()->whereKey($item)->exists())->toBeTrue()
        ->and($run->items()->whereKey($item)->exists())->toBeTrue()
        ->and($employee->payrollRunItems()->whereKey($item)->exists())->toBeTrue()
        ->and($item->payrollRun->is($run))->toBeTrue()
        ->and($item->employee->is($employee))->toBeTrue()
        ->and($item->status)->toBe(PayrollRunItemStatus::Calculated)
        ->and($item->basic_salary)->toBe('10000.00')
        ->and($item->gross_salary)->toBe('12500.00')
        ->and($item->net_salary)->toBe('12000.00');
});

it('stores payroll run item component snapshots', function () {
    $company = Company::factory()->create();
    $runItem = PayrollRunItem::factory()->create(['company_id' => $company->id]);
    $salaryComponent = SalaryComponent::factory()->create([
        'company_id' => $company->id,
        'type' => SalaryComponentType::Allowance,
        'name_ar' => 'بدل سكن',
        'name_en' => 'Housing allowance',
    ]);

    $component = PayrollRunItemComponent::factory()->create([
        'payroll_run_item_id' => $runItem->id,
        'salary_component_id' => $salaryComponent->id,
        'type' => SalaryComponentType::Allowance,
        'name_ar' => 'بدل سكن',
        'name_en' => 'Housing allowance',
        'amount' => 1500,
    ]);

    expect($runItem->components()->whereKey($component)->exists())->toBeTrue()
        ->and($salaryComponent->payrollRunItemComponents()->whereKey($component)->exists())->toBeTrue()
        ->and($component->payrollRunItem->is($runItem))->toBeTrue()
        ->and($component->salaryComponent->is($salaryComponent))->toBeTrue()
        ->and($component->type)->toBe(SalaryComponentType::Allowance)
        ->and($component->amount)->toBe('1500.00');
});

it('provides stable payroll enum labels in Arabic and English', function () {
    app()->setLocale('ar');

    expect(PayrollRunItemStatus::Paid->label())->toBe('مدفوعة')
        ->and(SalaryCalculationType::Percentage->label())->toBe('نسبة مئوية')
        ->and(SalaryPackageStatus::Active->label())->toBe('نشط');

    app()->setLocale('en');

    expect(PayrollRunItemStatus::Cancelled->label())->toBe('Cancelled')
        ->and(SalaryCalculationType::Fixed->label())->toBe('Fixed amount')
        ->and(SalaryPackageStatus::Inactive->label())->toBe('Inactive');
});
