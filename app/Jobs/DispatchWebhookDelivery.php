<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use App\Services\WebhookDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class DispatchWebhookDelivery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @return array<int, int> */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function __construct(public int $webhookDeliveryId) {}

    public function handle(WebhookDeliveryService $deliveryService): void
    {
        $delivery = WebhookDelivery::query()->findOrFail($this->webhookDeliveryId);
        $deliveryService->deliver($delivery);
    }

    public function failed(?Throwable $exception): void
    {
        $delivery = WebhookDelivery::query()->find($this->webhookDeliveryId);

        if ($delivery === null) {
            return;
        }

        $delivery->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => $exception?->getMessage(),
        ]);

        Log::error('Webhook delivery failed.', [
            'webhook_delivery_id' => $this->webhookDeliveryId,
            'company_id' => $delivery->company_id,
            'message' => $exception?->getMessage(),
        ]);
    }
}
