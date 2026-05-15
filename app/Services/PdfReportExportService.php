<?php

namespace App\Services;

use App\DTOs\ReportDefinition;
use App\DTOs\ReportExportPayload;
use App\DTOs\ReportFilter;
use App\Models\Company;
use App\Models\User;
use RuntimeException;

class PdfReportExportService
{
    public function available(): bool
    {
        return class_exists('Barryvdh\\DomPDF\\Facade\\Pdf') || class_exists('Dompdf\\Dompdf');
    }

    /**
     * @return array<string, mixed>
     */
    public function prepare(
        ReportDefinition $definition,
        ReportFilter $filter,
        ReportExportPayload $payload,
        ?User $generatedBy = null,
        ?Company $company = null,
    ): array {
        return [
            'format' => 'pdf',
            'available' => $this->available(),
            'required_package' => $this->available() ? null : 'barryvdh/laravel-dompdf or another approved Laravel PDF renderer',
            'report' => $definition->toArray(),
            'title' => $payload->title ?: $definition->name($filter->locale),
            'locale' => in_array($filter->locale, ['ar', 'en'], true) ? $filter->locale : 'ar',
            'direction' => $filter->locale === 'en' ? 'ltr' : 'rtl',
            'filters_summary' => $filter->toArray(),
            'kpi_cards' => $payload->kpis,
            'table' => [
                'columns' => $payload->columns,
                'rows' => $payload->rows,
            ],
            'chart' => $payload->chart,
            'generated_by' => $generatedBy?->name,
            'generated_at' => now()->toDateTimeString(),
            'company' => $company ? [
                'id' => $company->id,
                'name' => $company->name,
                'legal_name' => $company->legal_name,
                'currency' => $company->currency,
                'locale' => $company->locale,
            ] : null,
            'metadata' => $payload->metadata,
        ];
    }

    public function export(): never
    {
        throw new RuntimeException(__('reports.pdf.package_missing'));
    }
}
