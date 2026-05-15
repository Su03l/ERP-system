<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\DashboardWidget;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DashboardWidget>
 */
class DashboardWidgetFactory extends Factory
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
            'widget_key' => fake()->unique()->slug(2),
            'module' => 'hr',
            'title_ar' => 'مؤشر',
            'title_en' => 'Widget',
            'type' => 'kpi',
            'resolver' => 'hr.total_employees',
            'required_permission' => 'employees.view',
            'default_size' => 'medium',
            'metadata' => [],
        ];
    }
}
