<?php

use App\DTOs\KpiCollectionResult;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiResult;

it('serializes KPI results with formatting comparison trend unit and metadata', function () {
    $dateRange = KpiDateRange::fromDates('2026-01-01', '2026-01-31');
    $result = new KpiResult(
        key: 'attendance.attendance_rate',
        label: 'Attendance rate',
        value: 95.5,
        category: 'attendance',
        dateRange: $dateRange,
        formattedValue: '95.5%',
        comparisonValue: 90,
        trend: 'up',
        unit: 'percent',
        metadata: ['records' => 20],
    );

    expect($result->toArray())->toMatchArray([
        'key' => 'attendance.attendance_rate',
        'label' => 'Attendance rate',
        'value' => 95.5,
        'formatted_value' => '95.5%',
        'comparison_value' => 90,
        'trend' => 'up',
        'unit' => 'percent',
        'metadata' => ['records' => 20],
    ]);
});

it('serializes KPI collection results', function () {
    $collection = new KpiCollectionResult([
        new KpiResult('hr.total_employees', 'Total employees', 3),
    ]);

    expect($collection->toArray())->toHaveCount(1)
        ->and($collection->toArray()[0]['key'])->toBe('hr.total_employees');
});
