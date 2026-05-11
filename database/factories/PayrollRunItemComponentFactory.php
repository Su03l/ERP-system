<?php

namespace Database\Factories;

use App\Enums\SalaryComponentType;
use App\Models\PayrollRunItem;
use App\Models\PayrollRunItemComponent;
use App\Models\SalaryComponent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayrollRunItemComponent>
 */
class PayrollRunItemComponentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(SalaryComponentType::cases());

        return [
            'payroll_run_item_id' => PayrollRunItem::factory(),
            'salary_component_id' => fn (array $attributes): int => SalaryComponent::factory()->create([
                'company_id' => PayrollRunItem::query()->find($attributes['payroll_run_item_id'])?->company_id,
                'type' => $type,
            ])->id,
            'type' => $type,
            'name_ar' => fake()->words(2, true),
            'name_en' => fake()->words(2, true),
            'amount' => fake()->randomFloat(2, 100, 2000),
            'metadata' => [],
        ];
    }
}
