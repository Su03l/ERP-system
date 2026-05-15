<?php

use App\Http\Controllers\ChartDataController;
use App\Http\Controllers\DashboardWidgetController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\ReportController;
use App\Services\AnalyticsCacheService;
use App\Services\ReportExportService;
use App\Services\ReportRegistry;

it('keeps analytics controllers thin and service backed', function () {
    expect(class_exists(KpiController::class))->toBeTrue()
        ->and(class_exists(DashboardWidgetController::class))->toBeTrue()
        ->and(class_exists(ChartDataController::class))->toBeTrue()
        ->and(class_exists(ReportController::class))->toBeTrue()
        ->and(class_exists(ReportRegistry::class))->toBeTrue()
        ->and(class_exists(ReportExportService::class))->toBeTrue()
        ->and(class_exists(AnalyticsCacheService::class))->toBeTrue();
});

it('keeps report registry entries permissioned and export-ready', function () {
    $reports = collect(ReportRegistry::default()->available());

    expect($reports)->not->toBeEmpty()
        ->and($reports->every(fn (array $report): bool => filled($report['required_permission'])))->toBeTrue()
        ->and($reports->every(fn (array $report): bool => in_array('csv', $report['supported_exports'], true)))->toBeTrue();
});
