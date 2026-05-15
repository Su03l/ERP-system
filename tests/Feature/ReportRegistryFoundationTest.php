<?php

use App\Services\AttendanceExportQuery;
use App\Services\FinancialReportQueryService;
use App\Services\ReportRegistry;
use App\Services\SaasExportService;

it('registers core ERP report definitions and export formats', function () {
    $reports = collect(ReportRegistry::default()->available())->keyBy('key');

    expect($reports->keys())->toContain(
        'hr.employees',
        'attendance.records',
        'leave.requests',
        'payroll.runs',
        'accounting.financial',
        'assets.assets',
        'documents.expiry',
        'projects.projects',
        'saas.revenue',
    )
        ->and($reports['attendance.records']['resolver_class'])->toBe(AttendanceExportQuery::class)
        ->and($reports['accounting.financial']['resolver_class'])->toBe(FinancialReportQueryService::class)
        ->and($reports['saas.revenue']['resolver_class'])->toBe(SaasExportService::class)
        ->and($reports['payroll.runs']['supported_exports'])->toBe(['pdf', 'excel', 'csv']);
});

it('can answer whether a report supports an export format', function () {
    $registry = ReportRegistry::default();

    expect($registry->supportsExport('documents.expiry', 'csv'))->toBeTrue()
        ->and($registry->supportsExport('documents.expiry', 'xml'))->toBeFalse();
});
