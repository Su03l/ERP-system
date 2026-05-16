<?php

namespace App\Services;

use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Database\Eloquent\Model;

class WebhookEventDispatcher
{
    public function __construct(
        private readonly WebhookEventRegistry $registry,
        private readonly WebhookDeliveryService $deliveries,
    ) {}

    /** @return array<int, WebhookDelivery> */
    public function dispatch(string $eventName, Model $model, int $companyId): array
    {
        $payload = [
            'event' => $eventName,
            'data' => $this->registry->payload($eventName, $model),
        ];

        return WebhookEndpoint::query()
            ->forCompany($companyId)
            ->where('status', 'active')
            ->get()
            ->filter(fn (WebhookEndpoint $endpoint): bool => $endpoint->listensFor($eventName))
            ->map(function (WebhookEndpoint $endpoint) use ($eventName, $payload): WebhookDelivery {
                $delivery = $this->deliveries->createDelivery($endpoint, $eventName, $payload);
                $this->deliveries->queue($delivery);

                return $delivery;
            })
            ->values()
            ->all();
    }
}
