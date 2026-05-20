<?php

it('renders the kpi card component with title and value', function () {
    $view = $this->blade(
        '<x-kpi-card :title="$title" :value="$value" :module="$module" />',
        [
            'title' => 'Total Sales',
            'value' => '12,500',
            'module' => 'finance',
        ]
    );

    $view->assertSee('Total Sales');
    $view->assertSee('12,500');
    $view->assertSee('المالية والنشاط'); // Arabic label for 'finance'
});

it('renders correct accents and labels for modules', function (string $module, string $expectedLabel) {
    $view = $this->blade(
        '<x-kpi-card :title="$title" :value="$value" :module="$module" />',
        [
            'title' => 'Metric Title',
            'value' => '100',
            'module' => $module,
        ]
    );

    $view->assertSee('Metric Title');
    $view->assertSee('100');
    $view->assertSee($expectedLabel);
})->with([
    ['hr', 'الموارد البشرية'],
    ['payroll', 'الرواتب'],
    ['accounting', 'المالية والنشاط'],
    ['saas', 'الاشتراكات'],
    ['general', 'عام'],
]);

it('renders trend indicators with correct percentage and icons', function (string $trend, string $compVal, string $svgClassPattern) {
    $view = $this->blade(
        '<x-kpi-card title="Sales" value="450" :trend="$trend" :comparisonValue="$comparisonValue" />',
        [
            'trend' => $trend,
            'comparisonValue' => $compVal,
        ]
    );

    $view->assertSee($compVal);
    // Verify trend svg patterns or indicators render correctly
    $view->assertSee($svgClassPattern, false);
})->with([
    ['up', '+12%', 'd="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"'],
    ['down', '-5%', 'd="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"'],
    ['stable', '0%', 'd="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3"'],
]);

it('renders loading skeleton and does not render content when loading is true', function () {
    $view = $this->blade(
        '<x-kpi-card title="Sales" value="450" :loading="true" />'
    );

    $view->assertSee('animate-pulse');
    $view->assertDontSee('Sales');
    $view->assertDontSee('450');
});

it('renders empty state when empty is true or value is null', function () {
    $view = $this->blade(
        '<x-kpi-card title="Vacancies" :value="null" />'
    );

    $view->assertSee('—');
    $view->assertSee('لا توجد بيانات');
});

it('supports custom slots for custom icons', function () {
    $view = $this->blade(
        '<x-kpi-card title="Custom" value="100"><x-slot name="icon"><span class="custom-test-icon">🔍</span></x-slot></x-kpi-card>'
    );

    $view->assertSee('custom-test-icon');
    $view->assertSee('🔍');
});
