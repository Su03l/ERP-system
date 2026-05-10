<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveBalance>
 */
class LeaveBalanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $openingBalance = fake()->randomFloat(2, 0, 10);
        $accruedDays = fake()->randomFloat(2, 0, 20);
        $usedDays = fake()->randomFloat(2, 0, min(20, $openingBalance + $accruedDays));

        return [
            'company_id' => Company::factory(),
            'employee_id' => fn (array $attributes): int => Employee::factory()->create([
                'company_id' => $attributes['company_id'],
            ])->id,
            'leave_type_id' => fn (array $attributes): int => LeaveType::factory()->create([
                'company_id' => $attributes['company_id'],
            ])->id,
            'year' => now()->year,
            'opening_balance' => $openingBalance,
            'accrued_days' => $accruedDays,
            'used_days' => $usedDays,
            'remaining_days' => max(0, $openingBalance + $accruedDays - $usedDays),
            'metadata' => [],
        ];
    }
}
