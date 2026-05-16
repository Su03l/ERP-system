<?php

namespace App\Services;

use App\Jobs\DispatchWebhookDelivery;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WebhookDeliveryService
{
    public function createDelivery(WebhookEndpoint $endpoint, string $eventName, array $payload): WebhookDelivery
    {
        return WebhookDelivery::query()->create([
            'company_id' => $endpoint->company_id,
            'webhook_endpoint_id' => $endpoint->id,
            'event_name' => $eventName,
            'payload' => $this->sanitizePayload($payload),
            'status' => 'pending',
        ]);
    }

    public function queue(WebhookDelivery $delivery): void
    {
        DispatchWebhookDelivery::dispatch($delivery->id)->afterCommit();
    }

    public function deliver(WebhookDelivery $delivery): WebhookDelivery
    {
        $delivery->loadMissing('endpoint');
        $endpoint = $delivery->endpoint;

        if ($endpoint === null || $endpoint->status !== 'active') {
            $delivery->update([
                'status' => 'skipped',
                'error_message' => 'Webhook endpoint is not active.',
                'failed_at' => now(),
            ]);

            return $delivery->refresh();
        }

        $payload = $delivery->payload ?? [];
        $body = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $headers = [
            'X-Nawwat-Event' => $delivery->event_name,
            'X-Nawwat-Delivery' => (string) $delivery->id,
        ];

        if ($endpoint->secret_hash !== null) {
            $headers['X-Nawwat-Signature'] = hash_hmac('sha256', $body, $endpoint->secret_hash);
        }

        $delivery->increment('attempt_count');

        try {
            $response = Http::timeout(10)
                ->withHeaders($headers)
                ->post($endpoint->url, $payload);
        } catch (ConnectionException $exception) {
            return $this->markFailed($delivery, $endpoint, $exception->getMessage());
        }

        if ($response->successful()) {
            $delivery->update([
                'status' => 'delivered',
                'response_status' => $response->status(),
                'response_body' => Str::limit($response->body(), 1000),
                'delivered_at' => now(),
                'failed_at' => null,
                'error_message' => null,
            ]);
            $endpoint->update([
                'last_success_at' => now(),
                'failure_count' => 0,
            ]);

            return $delivery->refresh();
        }

        return $this->markFailed($delivery, $endpoint, "Webhook returned HTTP {$response->status()}.", $response->status(), $response->body());
    }

    private function markFailed(WebhookDelivery $delivery, WebhookEndpoint $endpoint, string $message, ?int $status = null, ?string $body = null): WebhookDelivery
    {
        $delivery->update([
            'status' => 'failed',
            'response_status' => $status,
            'response_body' => $body !== null ? Str::limit($body, 1000) : null,
            'next_retry_at' => now()->addMinutes(min(60, max(5, $delivery->attempt_count * 5))),
            'failed_at' => now(),
            'error_message' => Str::limit($message, 500),
        ]);
        $endpoint->increment('failure_count');
        $endpoint->forceFill(['last_failure_at' => now()])->save();

        return $delivery->refresh();
    }

    private function sanitizePayload(array $payload): array
    {
        return collect($payload)
            ->reject(fn (mixed $value, string|int $key): bool => in_array(strtolower((string) $key), ['secret', 'password', 'token'], true))
            ->all();
    }
}
