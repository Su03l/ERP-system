<?php

namespace App\DTOs;

class ChartData
{
    /**
     * @param  array<int, string>  $labels
     * @param  array<int, ChartDataset>  $datasets
     * @param  array<int, array<string, mixed>>  $series
     * @param  array<string, int|float|string|null>  $totals
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $type,
        public readonly array $labels = [],
        public readonly array $datasets = [],
        public readonly array $series = [],
        public readonly array $totals = [],
        public readonly array $metadata = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'labels' => $this->labels,
            'datasets' => array_map(
                fn (ChartDataset $dataset): array => $dataset->toArray(),
                $this->datasets,
            ),
            'series' => $this->series,
            'totals' => $this->totals,
            'metadata' => $this->metadata,
        ];
    }
}
