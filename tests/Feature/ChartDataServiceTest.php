<?php

use App\DTOs\ChartDataset;
use App\Services\ChartDataService;

it('builds chart-ready datasets with labels, totals, metadata, and locale', function () {
    app()->setLocale('en');

    $chart = app(ChartDataService::class)->bar(
        labels: ['Jan', 'Feb'],
        datasets: [
            new ChartDataset(label: 'Revenue', data: [100, 250], key: 'revenue'),
        ],
        totals: ['revenue' => 350],
        metadata: ['module' => 'accounting'],
    )->toArray();

    expect($chart['type'])->toBe('bar')
        ->and($chart['labels'])->toBe(['Jan', 'Feb'])
        ->and($chart['datasets'][0]['key'])->toBe('revenue')
        ->and($chart['datasets'][0]['data'])->toBe([100, 250])
        ->and($chart['totals']['revenue'])->toBe(350)
        ->and($chart['metadata']['locale'])->toBe('en')
        ->and($chart['metadata']['module'])->toBe('accounting');
});

it('supports pie and donut totals without module-specific logic', function () {
    $chart = app(ChartDataService::class)->donut(['Active', 'Inactive'], [7, 3], 'Employees')->toArray();

    expect($chart['type'])->toBe('donut')
        ->and($chart['totals']['total'])->toBe(10.0)
        ->and($chart['datasets'][0]['label'])->toBe('Employees');
});

it('localizes labels with Arabic fallback', function () {
    $service = app(ChartDataService::class);

    expect($service->localizedLabel('الموظفون', 'Employees', 'en'))->toBe('Employees')
        ->and($service->localizedLabel('الموظفون', null, 'en'))->toBe('الموظفون')
        ->and($service->localizedLabel('الموظفون', 'Employees', 'ar'))->toBe('الموظفون');
});
