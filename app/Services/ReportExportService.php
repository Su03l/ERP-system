<?php

namespace App\Services;

use App\DTOs\ReportExportPayload;
use App\DTOs\ReportFilter;
use App\Jobs\ProcessReportExportJob;
use App\Models\ExportJob;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ReportExportService
{
    public function __construct(
        private readonly ReportSpreadsheetExportService $spreadsheets,
        private readonly PdfReportExportService $pdf,
    ) {}

    public function request(string $reportKey, ReportFilter $filter, User $user, bool $queued = true): ExportJob
    {
        $definition = ReportRegistry::default()->definition($reportKey);
        $companyId = $filter->companyId ?? $user->company_id;

        if ($companyId !== null && $user->company_id !== null && $companyId !== $user->company_id) {
            throw new RuntimeException('Report export company scope does not match the current user.');
        }

        $job = ExportJob::query()->create([
            'company_id' => $companyId,
            'user_id' => $user->id,
            'status' => 'pending',
            'file_path' => $this->spreadsheets->safeFileName($definition->key, $filter->exportFormat ?? 'csv'),
            'entity_type' => $definition->key,
            'module_key' => $definition->module,
            'processed_rows' => 0,
            'failed_rows' => 0,
            'total_rows' => 0,
        ]);

        if ($queued) {
            ProcessReportExportJob::dispatch($job->id)->afterCommit();

            return $job;
        }

        return $this->process($job);
    }

    public function process(ExportJob $job): ExportJob
    {
        $definition = ReportRegistry::default()->definition($job->entity_type);
        $user = User::query()->findOrFail($job->user_id);
        Auth::login($user);

        $job->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        $format = $this->formatFromPath($job->file_path) ?? 'csv';
        $rows = $this->resolveRows($definition->key, $user);
        $columns = $this->columnsFromRows($rows);
        $filter = new ReportFilter(companyId: $job->company_id, exportFormat: $format);
        $fileName = $this->spreadsheets->safeFileName($definition->key, $format);
        $path = "exports/reports/{$job->company_id}/{$fileName}";

        if ($format === 'csv') {
            Storage::disk('local')->put($path, $this->spreadsheets->toCsv($columns, $rows));
        } else {
            $payload = new ReportExportPayload(
                title: $definition->name(),
                columns: $columns,
                rows: $rows,
            );

            $placeholder = $format === 'pdf'
                ? $this->pdf->prepare($definition, $filter, $payload, $user, $user->company)
                : $this->spreadsheets->excelPlaceholder($definition, $filter);

            Storage::disk('local')->put($path.'.json', json_encode($placeholder, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $path .= '.json';
        }

        $job->update([
            'status' => 'completed',
            'file_path' => $path,
            'processed_rows' => count($rows),
            'failed_rows' => 0,
            'total_rows' => count($rows),
            'finished_at' => now(),
        ]);

        return $job->refresh();
    }

    public function markFailed(ExportJob $job, string $message): void
    {
        $job->update([
            'status' => 'failed',
            'error_summary' => ['message' => Str::limit($message, 500)],
            'finished_at' => now(),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolveRows(string $reportKey, User $user): array
    {
        return match ($reportKey) {
            'hr.employees' => app(EmployeeExportQuery::class)->export([], $user)['rows'],
            'attendance.records' => app(AttendanceExportQuery::class)->export([], $user)['rows'],
            'payroll.runs' => app(PayrollExportService::class)->runSummary([], $user)['rows'],
            'assets.assets' => app(AssetExportQuery::class)->rows([], $user),
            'documents.expiry' => app(DocumentExportQuery::class)->expiring([], $user),
            'projects.projects' => app(ProjectCrmExportQuery::class)->projects($user->company_id),
            'saas.revenue' => [$this->flattenMetrics(app(SaasExportService::class)->revenueMetrics($user))],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $metrics
     * @return array<string, mixed>
     */
    private function flattenMetrics(array $metrics): array
    {
        return collect($metrics)
            ->map(fn (mixed $value): mixed => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value)
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array{key: string, label: string}>
     */
    private function columnsFromRows(array $rows): array
    {
        return collect($rows[0] ?? [])
            ->keys()
            ->map(fn (string $key): array => ['key' => $key, 'label' => Str::headline($key)])
            ->values()
            ->all();
    }

    private function formatFromPath(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        return pathinfo($path, PATHINFO_EXTENSION) ?: null;
    }
}
