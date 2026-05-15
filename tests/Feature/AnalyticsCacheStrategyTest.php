<?php

use App\DTOs\KpiDateRange;
use App\DTOs\ReportFilter;
use App\Services\AnalyticsCacheService;

it('builds isolated cache keys by type scope date range and filters', function () {
    $service = app(AnalyticsCacheService::class);
    $range = KpiDateRange::fromDates('2026-05-01', '2026-05-31');

    $companyKey = $service->key('kpi', 'hr.total_employees', 10, [
        'date_range' => $range->toArray(),
        'filters' => ['department_id' => 1],
    ]);
    $otherCompanyKey = $service->key('kpi', 'hr.total_employees', 11, [
        'filters' => ['department_id' => 1],
        'date_range' => $range->toArray(),
    ]);
    $platformKey = $service->key('report_summary', 'saas.revenue', null, ReportFilter::fromArray([])->toArray());

    expect($companyKey)->toContain('company:10')
        ->and($otherCompanyKey)->toContain('company:11')
        ->and($platformKey)->toContain('platform')
        ->and($companyKey)->not->toBe($otherCompanyKey);
});
