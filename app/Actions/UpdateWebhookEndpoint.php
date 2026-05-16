<?php

namespace App\Actions;

use App\Models\User;
use App\Models\WebhookEndpoint;
use App\Services\AuditLogger;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UpdateWebhookEndpoint
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(WebhookEndpoint $endpoint, array $data, ?User $actor = null): WebhookEndpoint
    {
        $actor ??= Auth::user();

        abort_unless($actor instanceof User && $actor->company_id === $endpoint->company_id && $actor->hasPermission('webhooks.update', $endpoint->company_id), 404);

        return DB::transaction(function () use ($endpoint, $data, $actor): WebhookEndpoint {
            $oldValues = Arr::except($endpoint->attributesToArray(), ['secret_hash']);
            $payload = Arr::except($data, ['secret']);

            if (array_key_exists('secret', $data)) {
                $payload['secret_hash'] = filled($data['secret']) ? hash('sha256', $data['secret']) : null;
            }

            $endpoint->update($payload);
            $this->auditLogger->log('webhook_endpoint.updated', $endpoint, oldValues: $oldValues, newValues: Arr::except($endpoint->attributesToArray(), ['secret_hash']), user: $actor, company: $endpoint->company_id);

            return $endpoint->refresh();
        });
    }
}
