<?php

namespace Database\Factories;

use App\Enums\LeaveRequestStatus;
use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+30 days');
        $endDate = fake()->dateTimeBetween($startDate, '+45 days');

        return [
            'company_id' => Company::factory(),
            'employee_id' => fn (array $attributes): int => Employee::factory()->create([
                'company_id' => $attributes['company_id'],
            ])->id,
            'leave_type_id' => fn (array $attributes): int => LeaveType::factory()->create([
                'company_id' => $attributes['company_id'],
            ])->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => 1,
            'reason' => fake()->optional()->sentence(),
            'status' => LeaveRequestStatus::Draft->value,
            'workflow_instance_id' => null,
            'approved_by' => null,
            'approved_at' => null,
            'rejected_reason' => null,
            'metadata' => [],
        ];
    }
}
