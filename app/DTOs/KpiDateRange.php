<?php

namespace App\DTOs;

use Carbon\CarbonImmutable;

class KpiDateRange
{
    public function __construct(
        public readonly CarbonImmutable $start,
        public readonly CarbonImmutable $end,
    ) {
        if ($this->start->greaterThan($this->end)) {
            throw new \InvalidArgumentException('The KPI date range start must be before or equal to the end.');
        }
    }

    public static function fromDates(string $start, string $end): self
    {
        return new self(
            CarbonImmutable::parse($start)->startOfDay(),
            CarbonImmutable::parse($end)->endOfDay(),
        );
    }

    /**
     * @return array{start: string, end: string}
     */
    public function toArray(): array
    {
        return [
            'start' => $this->start->toDateString(),
            'end' => $this->end->toDateString(),
        ];
    }
}
