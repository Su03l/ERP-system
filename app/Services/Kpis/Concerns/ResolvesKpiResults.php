<?php

namespace App\Services\Kpis\Concerns;

use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;

trait ResolvesKpiResults
{
    public function key(): string
    {
        return $this->definition()->key;
    }

    public function label(): string
    {
        return $this->definition()->label();
    }

    public function module(): string
    {
        return $this->definition()->module;
    }

    protected function definitionFor(
        string $key,
        string $module,
        string $labelAr,
        ?string $labelEn,
        ?string $requiredPermission,
        ?string $descriptionAr = null,
        ?string $descriptionEn = null,
        bool $supportsDateRange = true,
        ?string $defaultDateRange = 'this_month',
    ): KpiDefinition {
        return new KpiDefinition(
            key: $key,
            module: $module,
            labelAr: $labelAr,
            labelEn: $labelEn,
            descriptionAr: $descriptionAr,
            descriptionEn: $descriptionEn,
            requiredPermission: $requiredPermission,
            resolverClass: static::class,
            supportsDateRange: $supportsDateRange,
            defaultDateRange: $defaultDateRange,
        );
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    protected function result(
        KpiDateRange $dateRange,
        int|float|string|null $value,
        ?string $formattedValue = null,
        int|float|string|null $comparisonValue = null,
        ?string $trend = null,
        ?string $unit = null,
        ?array $metadata = null,
    ): KpiResult {
        return new KpiResult(
            key: $this->key(),
            label: $this->label(),
            value: $value,
            category: $this->module(),
            dateRange: $dateRange,
            formattedValue: $formattedValue,
            comparisonValue: $comparisonValue,
            trend: $trend,
            unit: $unit,
            metadata: $metadata,
        );
    }

    protected function percentage(int|float $part, int|float $total): float
    {
        if ((float) $total === 0.0) {
            return 0.0;
        }

        return round(((float) $part / (float) $total) * 100, 2);
    }
}
