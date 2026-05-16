<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\WebhookDeliveryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'webhook_endpoint_id',
    'event_name',
    'payload',
    'response_status',
    'response_body',
    'attempt_count',
    'status',
    'next_retry_at',
    'delivered_at',
    'failed_at',
    'error_message',
    'metadata',
])]
class WebhookDelivery extends Model
{
    /** @use HasFactory<WebhookDeliveryFactory> */
    use BelongsToCompany, HasFactory;

    /** @return BelongsTo<WebhookEndpoint, $this> */
    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(WebhookEndpoint::class, 'webhook_endpoint_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'response_status' => 'integer',
            'attempt_count' => 'integer',
            'next_retry_at' => 'datetime',
            'delivered_at' => 'datetime',
            'failed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
