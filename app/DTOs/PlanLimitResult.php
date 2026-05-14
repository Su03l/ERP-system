<?php

namespace App\DTOs;

final readonly class PlanLimitResult
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public bool $allowed,
        public string $key,
        public string $message,
        public int|float|null $limit = null,
        public int|float|null $current = null,
        public array $metadata = [],
    ) {}

    /**
     * @return array{allowed: bool, key: string, message: string, limit: int|float|null, current: int|float|null, metadata: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'allowed' => $this->allowed,
            'key' => $this->key,
            'message' => $this->message,
            'limit' => $this->limit,
            'current' => $this->current,
            'metadata' => $this->metadata,
        ];
    }
}
