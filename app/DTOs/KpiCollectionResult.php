<?php

namespace App\DTOs;

class KpiCollectionResult
{
    /**
     * @param  array<int, KpiResult>  $results
     */
    public function __construct(public readonly array $results) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function toArray(): array
    {
        return array_map(
            fn (KpiResult $result): array => $result->toArray(),
            $this->results,
        );
    }
}
