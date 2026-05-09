<?php

namespace Database\Factories;

use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceRecord>
 */
class AttendanceRecordFactory extends Factory
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
            'employee_id' => fn (array $attributes): int => Employee::factory()->create([
                'company_id' => $attributes['company_id'],
            ])->id,
            'attendance_date' => fake()->date(),
            'clock_in_at' => fake()->optional()->dateTime(),
            'clock_out_at' => fake()->optional()->dateTime(),
            'clock_in_ip' => fake()->optional()->ipv4(),
            'clock_out_ip' => fake()->optional()->ipv4(),
            'status' => 'present',
            'source' => fake()->optional()->randomElement(['manual', 'web', 'device', 'import']),
            'late_minutes' => 0,
            'overtime_minutes' => 0,
            'total_work_minutes' => fake()->optional()->numberBetween(60, 600),
            'notes' => fake()->optional()->sentence(),
            'metadata' => [],
        ];
    }
}
