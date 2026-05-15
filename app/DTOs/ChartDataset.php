<?php

namespace App\DTOs;

class ChartDataset
{
    /**
     * @param  array<int, int|float|string|null>  $data
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $label,
        public readonly array $data,
        public readonly ?string $key = null,
        public readonly ?string $type = null,
        public readonly array $metadata = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'type' => $this->type,
            'data' => $this->data,
            'metadata' => $this->metadata,
        ];
    }
}
