<?php

use App\DTOs\ReportFilter;
use App\Models\Company;
use App\Models\ExportJob;
use App\Models\User;
use App\Services\ReportRegistry;
use App\Services\ReportSpreadsheetExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates native CSV output with localized headers and safe names', function () {
    $service = app(ReportSpreadsheetExportService::class);

    $csv = $service->toCsv(
        columns: [
            ['key' => 'employee_number', 'label' => 'رقم الموظف'],
            ['key' => 'name', 'label' => 'الاسم'],
        ],
        rows: [
            ['employee_number' => 'E-001', 'name' => 'Ahmed'],
        ],
    );

    expect($csv)->toContain('رقم الموظف')
        ->and($csv)->toContain('E-001')
        ->and($service->safeFileName('hr.employees/report', 'csv', '20260515_120000'))->toBe('hr-employees-report-20260515_120000.csv')
        ->and($service->safeFileName('payroll.runs', 'excel', '20260515_120000'))->toBe('payroll-runs-20260515_120000.xlsx');
});

it('tracks report exports through export jobs', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $definition = ReportRegistry::default()->definition('assets.assets');
    $filter = ReportFilter::fromArray(['export_format' => 'csv'], $company->id);

    $job = app(ReportSpreadsheetExportService::class)->trackExport(
        definition: $definition,
        filter: $filter,
        user: $user,
        company: $company,
        filePath: 'exports/assets-report.csv',
        totalRows: 12,
    );

    expect($job)->toBeInstanceOf(ExportJob::class)
        ->and($job->company_id)->toBe($company->id)
        ->and($job->entity_type)->toBe('assets.assets')
        ->and($job->module_key)->toBe('assets')
        ->and($job->processed_rows)->toBe(12)
        ->and($job->finished_at)->not->toBeNull();
});
