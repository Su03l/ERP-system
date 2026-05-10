<?php

namespace Database\Factories;

use App\Enums\PayrollCycleType;
use App\Models\Company;
use App\Models\PayrollSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayrollSetting>
 */
class PayrollSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'payroll_cycle_type' => PayrollCycleType::Monthly,
            'default_pay_day' => 1,
            'overtime_calculation_enabled' => true,
            'absence_deduction_enabled' => true,
            'late_deduction_enabled' => true,
            'default_currency' => 'SAR',
            'payslip_language' => 'ar',
            'payroll_approval_required' => true,
            'metadata' => [],
        ];
    }
}
