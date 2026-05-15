<?php

namespace App\Services;

use App\DTOs\ReportDefinition;
use App\DTOs\ReportFilter;
use App\Models\Company;
use App\Models\ExportJob;
use App\Models\User;
use Illuminate\Support\Str;
use RuntimeException;

class ReportSpreadsheetExportService
{
    /**
     * @param  array<int, array{key: string, label: string}>  $columns
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function toCsv(array $columns, array $rows): string
    {
        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            throw new RuntimeException('Unable to open temporary CSV stream.');
        }

        fputcsv($handle, array_map(fn (array $column): string => $column['label'], $columns));

        foreach ($rows as $row) {
            fputcsv($handle, array_map(fn (array $column): mixed => $row[$column['key']] ?? null, $columns));
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        if ($csv === false) {
            throw new RuntimeException('Unable to read temporary CSV stream.');
        }

        return $csv;
    }

    public function safeFileName(string $reportKey, string $format, ?string $timestamp = null): string
    {
        $extension = strtolower($format) === 'excel' ? 'xlsx' : strtolower($format);
        $timestamp ??= now()->format('Ymd_His');

        return Str::of($reportKey)
            ->replace(['.', '/', '\\'], '-')
            ->slug()
            ->append("-{$timestamp}.{$extension}")
            ->toString();
    }

    /**
     * @return array<string, mixed>
     */
    public function excelPlaceholder(ReportDefinition $definition, ReportFilter $filter): array
    {
        return [
            'format' => 'excel',
            'available' => false,
            'required_package' => 'maatwebsite/excel or another approved Laravel Excel renderer',
            'report' => $definition->toArray(),
            'filters' => $filter->toArray(),
        ];
    }

    public function trackExport(
        ReportDefinition $definition,
        ReportFilter $filter,
        User $user,
        ?Company $company,
        string $filePath,
        int $totalRows,
        string $status = 'completed',
    ): ExportJob {
        return ExportJob::query()->create([
            'company_id' => $company?->id ?? $filter->companyId,
            'user_id' => $user->id,
            'status' => $status,
            'file_path' => $filePath,
            'entity_type' => $definition->key,
            'module_key' => $definition->module,
            'processed_rows' => $status === 'completed' ? $totalRows : 0,
            'failed_rows' => 0,
            'total_rows' => $totalRows,
            'started_at' => now(),
            'finished_at' => $status === 'completed' ? now() : null,
        ]);
    }
}
