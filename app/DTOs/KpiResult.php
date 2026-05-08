<?php

namespace App\DTOs;

class KpiResult
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly int|float|string|null $value,
        public readonly string $category,
        public readonly KpiDateRange $dateRange,
        public readonly array $metadata = [],
    ) {}

    /**
     * @return array{key: string, label: string, value: int|float|string|null, category: string, date_range: array{start: string, end: string}, metadata: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'value' => $this->value,
            'category' => $this->category,
            'date_range' => $this->dateRange->toArray(),
            'metadata' => $this->metadata,
        ];
    }
}
