<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WebhookDelivery>
 */
class WebhookDeliveryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'webhook_endpoint_id' => WebhookEndpoint::factory(),
            'event_name' => 'customer.created',
            'payload' => ['id' => fake()->numberBetween(1, 1000)],
            'response_status' => null,
            'response_body' => null,
            'attempt_count' => 0,
            'status' => 'pending',
            'next_retry_at' => null,
            'delivered_at' => null,
            'failed_at' => null,
            'error_message' => null,
            'metadata' => null,
        ];
    }
}
