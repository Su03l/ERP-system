<?php

namespace App\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;

class ReportFilter
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public readonly ?int $companyId = null,
        public readonly ?CarbonImmutable $dateFrom = null,
        public readonly ?CarbonImmutable $dateTo = null,
        public readonly string $locale = 'ar',
        public readonly ?string $exportFormat = null,
        public readonly array $filters = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, ?int $defaultCompanyId = null): self
    {
        $locale = in_array(($data['locale'] ?? 'ar'), ['ar', 'en'], true) ? $data['locale'] ?? 'ar' : 'ar';

        return new self(
            companyId: isset($data['company_id']) ? (int) $data['company_id'] : $defaultCompanyId,
            dateFrom: self::date($data['date_from'] ?? null),
            dateTo: self::date($data['date_to'] ?? null),
            locale: $locale,
            exportFormat: $data['export_format'] ?? null,
            filters: Arr::except($data, ['company_id', 'date_from', 'date_to', 'locale', 'export_format']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function normalizedFilters(): array
    {
        return [
            ...$this->filters,
            'date_from' => $this->dateFrom?->toDateString(),
            'date_to' => $this->dateTo?->toDateString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'company_id' => $this->companyId,
            'date_from' => $this->dateFrom?->toDateString(),
            'date_to' => $this->dateTo?->toDateString(),
            'locale' => $this->locale,
            'export_format' => $this->exportFormat,
            'filters' => $this->filters,
        ];
    }

    private static function date(mixed $value): ?CarbonImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        return CarbonImmutable::parse($value)->startOfDay();
    }
}
