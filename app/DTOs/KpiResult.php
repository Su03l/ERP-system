<?php

namespace App\DTOs;

class KpiResult
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly int|float|string|null $value,
        public readonly ?string $category = null,
        public readonly ?KpiDateRange $dateRange = null,
        public readonly ?string $formattedValue = null,
        public readonly int|float|string|null $comparisonValue = null,
        public readonly ?string $trend = null,
        public readonly ?string $unit = null,
        public readonly ?array $metadata = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'value' => $this->value,
            'category' => $this->category,
            'date_range' => $this->dateRange?->toArray(),
            'formatted_value' => $this->formattedValue,
            'comparison_value' => $this->comparisonValue,
            'trend' => $this->trend,
            'unit' => $this->unit,
            'metadata' => $this->metadata,
        ];
    }
}
