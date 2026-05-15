<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\DashboardPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DashboardPreference>
 */
class DashboardPreferenceFactory extends Factory
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
            'user_id' => fn (array $attributes): int => User::factory()->create([
                'company_id' => $attributes['company_id'],
            ])->id,
            'dashboard_key' => 'default',
            'selected_widgets' => ['hr.total_employees'],
            'widget_order' => ['hr.total_employees'],
            'hidden_widgets' => [],
            'filters' => [],
            'metadata' => [],
        ];
    }
}
