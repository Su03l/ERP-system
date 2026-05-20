<?php

it('renders the chart wrapper component with title and badge', function () {
    $view = $this->blade(
        '<x-chart-wrapper title="Monthly Operations" type="bar" module="hr" :labels="[\'Jan\', \'Feb\']" :values="[10, 20]" />'
    );

    $view->assertSee('Monthly Operations');
    $view->assertSee('الموارد البشرية'); // Arabic label for HR module
});

it('renders bar chart with dynamic vertical bars', function () {
    $view = $this->blade(
        '<x-chart-wrapper title="Employees" type="bar" :labels="[\'HR\', \'IT\']" :values="[150, 300]" />'
    );

    // Verify it lists the labels
    $view->assertSee('HR');
    $view->assertSee('IT');
    // Verify it renders the max value logic
    $view->assertSee('150');
    $view->assertSee('300');
});

it('renders donut chart with dynamic SVG ring segments and legend values', function () {
    $view = $this->blade(
        '<x-chart-wrapper title="Department Count" type="donut" :labels="[\'Sales\', \'Support\']" :values="[10, 40]" />'
    );

    // Verify center total count sum is calculated (10 + 40 = 50)
    $view->assertSee('50');
    // Verify side legends display percentage (10/50 = 20%, 40/50 = 80%)
    $view->assertSee('Sales');
    $view->assertSee('Support');
    $view->assertSee('20%');
    $view->assertSee('80%');
    // Verify circular path elements are generated
    $view->assertSee('stroke-dasharray', false);
    $view->assertSee('stroke-dashoffset', false);
});

it('renders line and area charts with calculated SVG coordinates', function (string $type, bool $expectAreaPath) {
    $view = $this->blade(
        '<x-chart-wrapper title="Growth" :type="$type" :labels="[\'M1\', \'M2\', \'M3\']" :values="[100, 250, 150]" />',
        ['type' => $type]
    );

    $view->assertSee('M1');
    $view->assertSee('M2');
    $view->assertSee('M3');

    // Verify that points coordinates are plotted
    $view->assertSee('<circle cx=', false);
    $view->assertSee('cy=', false);

    if ($expectAreaPath) {
        $view->assertSee('<path d=', false);
        $view->assertSee('fill="rgba(', false); // Area gradient color representation
    } else {
        $view->assertDontSee('fill="rgba(', false);
    }
})->with([
    ['line', false],
    ['area', true],
]);

it('renders loading skeleton specific to type', function (string $type, string $expectedSkeletonText) {
    $view = $this->blade(
        '<x-chart-wrapper title="Stats" :type="$type" :loading="true" />',
        ['type' => $type]
    );

    $view->assertSee('animate-pulse');
    $view->assertSee($expectedSkeletonText, false);
    $view->assertSee('Stats');
})->with([
    ['donut', 'rounded-full border-8 border-slate-200'],
    ['bar', 'rounded-t-md w-full'],
    ['line', 'border-t-2 border-dashed border-slate-200'],
]);

it('renders empty no-data state when values are empty', function () {
    $view = $this->blade(
        '<x-chart-wrapper title="Leaves" type="bar" :labels="[]" :values="[]" />'
    );

    $view->assertSee('لا توجد بيانات متاحة حالياً');
});

it('supports custom slots for action buttons', function () {
    $view = $this->blade(
        '<x-chart-wrapper title="Interactive" type="bar" :labels="[\'A\']" :values="[10]"><x-slot name="actions"><button class="btn-test">Refresh</button></x-slot></x-chart-wrapper>'
    );

    $view->assertSee('btn-test');
    $view->assertSee('Refresh');
});
