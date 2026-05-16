<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\WebhookEndpointFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'name',
    'url',
    'secret_hash',
    'events',
    'status',
    'last_success_at',
    'last_failure_at',
    'failure_count',
    'metadata',
])]
#[Hidden(['secret_hash'])]
class WebhookEndpoint extends Model
{
    /** @use HasFactory<WebhookEndpointFactory> */
    use BelongsToCompany, HasFactory;

    /** @return HasMany<WebhookDelivery, $this> */
    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    public function listensFor(string $eventName): bool
    {
        return in_array('*', $this->events ?? [], true) || in_array($eventName, $this->events ?? [], true);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'events' => 'array',
            'last_success_at' => 'datetime',
            'last_failure_at' => 'datetime',
            'failure_count' => 'integer',
            'metadata' => 'array',
        ];
    }
}
