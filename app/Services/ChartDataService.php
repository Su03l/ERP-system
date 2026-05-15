<?php

namespace App\Services;

use App\DTOs\ChartData;
use App\DTOs\ChartDataset;
use InvalidArgumentException;

class ChartDataService
{
    private const SUPPORTED_TYPES = [
        'line',
        'bar',
        'pie',
        'donut',
        'area',
    ];

    /**
     * @param  array<int, string>  $labels
     * @param  array<int, ChartDataset|array<string, mixed>>  $datasets
     * @param  array<int, array<string, mixed>>  $series
     * @param  array<string, int|float|string|null>  $totals
     * @param  array<string, mixed>  $metadata
     */
    public function make(
        string $type,
        array $labels = [],
        array $datasets = [],
        array $series = [],
        array $totals = [],
        array $metadata = [],
    ): ChartData {
        if (! in_array($type, self::SUPPORTED_TYPES, true)) {
            throw new InvalidArgumentException("Chart type [{$type}] is not supported.");
        }

        return new ChartData(
            type: $type,
            labels: $labels,
            datasets: array_map(fn (ChartDataset|array $dataset): ChartDataset => $this->dataset($dataset), $datasets),
            series: $series,
            totals: $totals,
            metadata: [
                'locale' => app()->getLocale(),
                ...$metadata,
            ],
        );
    }

    /**
     * @param  array<int, string>  $labels
     * @param  array<int, ChartDataset|array<string, mixed>>  $datasets
     * @param  array<string, int|float|string|null>  $totals
     * @param  array<string, mixed>  $metadata
     */
    public function line(array $labels, array $datasets, array $totals = [], array $metadata = []): ChartData
    {
        return $this->make('line', $labels, $datasets, totals: $totals, metadata: $metadata);
    }

    /**
     * @param  array<int, string>  $labels
     * @param  array<int, ChartDataset|array<string, mixed>>  $datasets
     * @param  array<string, int|float|string|null>  $totals
     * @param  array<string, mixed>  $metadata
     */
    public function bar(array $labels, array $datasets, array $totals = [], array $metadata = []): ChartData
    {
        return $this->make('bar', $labels, $datasets, totals: $totals, metadata: $metadata);
    }

    /**
     * @param  array<int, string>  $labels
     * @param  array<int, int|float|string|null>  $values
     * @param  array<string, mixed>  $metadata
     */
    public function pie(array $labels, array $values, string $label, array $metadata = []): ChartData
    {
        return $this->make('pie', $labels, [
            new ChartDataset(label: $label, data: $values),
        ], totals: ['total' => array_sum(array_map('floatval', $values))], metadata: $metadata);
    }

    /**
     * @param  array<int, string>  $labels
     * @param  array<int, int|float|string|null>  $values
     * @param  array<string, mixed>  $metadata
     */
    public function donut(array $labels, array $values, string $label, array $metadata = []): ChartData
    {
        return $this->make('donut', $labels, [
            new ChartDataset(label: $label, data: $values),
        ], totals: ['total' => array_sum(array_map('floatval', $values))], metadata: $metadata);
    }

    public function localizedLabel(string $labelAr, ?string $labelEn = null, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === 'en' ? $labelEn ?? $labelAr : $labelAr;
    }

    /**
     * @param  ChartDataset|array{label: string, data: array<int, int|float|string|null>, key?: string|null, type?: string|null, metadata?: array<string, mixed>}  $dataset
     */
    private function dataset(ChartDataset|array $dataset): ChartDataset
    {
        if ($dataset instanceof ChartDataset) {
            return $dataset;
        }

        return new ChartDataset(
            label: $dataset['label'],
            data: $dataset['data'],
            key: $dataset['key'] ?? null,
            type: $dataset['type'] ?? null,
            metadata: $dataset['metadata'] ?? [],
        );
    }
}
