<?php

namespace Database\Factories;

use App\Enums\PayrollPeriodStatus;
use App\Models\Company;
use App\Models\PayrollPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayrollPeriod>
 */
class PayrollPeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsOn = fake()->dateTimeBetween('-1 year', '+1 year')->format('Y-m-01');
        $endsOn = now()->parse($startsOn)->endOfMonth()->toDateString();

        return [
            'company_id' => Company::factory(),
            'name_ar' => 'فترة رواتب '.now()->parse($startsOn)->format('Y-m'),
            'name_en' => 'Payroll period '.now()->parse($startsOn)->format('Y-m'),
            'starts_on' => $startsOn,
            'ends_on' => $endsOn,
            'pay_date' => now()->parse($endsOn)->addDays(5)->toDateString(),
            'status' => PayrollPeriodStatus::Draft,
            'closed_at' => null,
            'closed_by' => null,
            'metadata' => [],
        ];
    }
}
