<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\WebhookEndpoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WebhookEndpoint>
 */
class WebhookEndpointFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->words(2, true),
            'url' => fake()->url(),
            'secret_hash' => hash('sha256', fake()->password(32)),
            'events' => ['customer.created'],
            'status' => 'active',
            'last_success_at' => null,
            'last_failure_at' => null,
            'failure_count' => 0,
            'metadata' => null,
        ];
    }
}
