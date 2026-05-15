<?php

use App\DTOs\ReportExportPayload;
use App\DTOs\ReportFilter;
use App\Models\Company;
use App\Models\User;
use App\Services\PdfReportExportService;
use App\Services\ReportRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('prepares PDF export payloads without requiring a PDF package', function () {
    $company = Company::factory()->create(['name' => 'Nawwat']);
    $user = User::factory()->for($company)->create(['name' => 'Report User']);
    $definition = ReportRegistry::default()->definition('hr.employees');
    $filter = ReportFilter::fromArray(['locale' => 'ar'], $company->id);
    $payload = new ReportExportPayload(
        title: $definition->name('ar'),
        kpis: [['label' => 'Total', 'value' => 5]],
        columns: [['key' => 'employee_number', 'label' => 'Employee #']],
        rows: [['employee_number' => 'E-001']],
        chart: ['type' => 'bar'],
    );

    $prepared = app(PdfReportExportService::class)->prepare($definition, $filter, $payload, $user, $company);

    expect($prepared['format'])->toBe('pdf')
        ->and($prepared['direction'])->toBe('rtl')
        ->and($prepared['company']['name'])->toBe('Nawwat')
        ->and($prepared['table']['rows'][0]['employee_number'])->toBe('E-001')
        ->and($prepared['required_package'])->not->toBeNull();
});
