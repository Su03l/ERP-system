<?php

namespace App\DTOs;

class ReportExportPayload
{
    /**
     * @param  array<int, array<string, mixed>>  $kpis
     * @param  array<int, array<string, mixed>>  $columns
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>|null  $chart
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $title,
        public readonly array $kpis = [],
        public readonly array $columns = [],
        public readonly array $rows = [],
        public readonly ?array $chart = null,
        public readonly array $metadata = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'kpis' => $this->kpis,
            'columns' => $this->columns,
            'rows' => $this->rows,
            'chart' => $this->chart,
            'metadata' => $this->metadata,
        ];
    }
}
