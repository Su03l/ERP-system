<?php

beforeEach(function () {
    $this->headers = [
        'name' => ['label' => 'الاسم', 'sortable' => true],
        'email' => ['label' => 'البريد الإلكتروني', 'sortable' => true],
        'status' => ['label' => 'الحالة', 'sortable' => false],
    ];

    $this->rows = [
        ['id' => 1, 'name' => 'أحمد علي', 'email' => 'ahmed@nawwat.com', 'status' => 'Active'],
        ['id' => 2, 'name' => 'سارة حسن', 'email' => 'sara@nawwat.com', 'status' => 'Pending'],
        ['id' => 3, 'name' => 'خالد محمد', 'email' => 'khaled@nawwat.com', 'status' => 'Rejected'],
    ];
});

test('it renders table headers and standard row values successfully', function () {
    $view = $this->blade(
        '<x-table-wrapper :headers="$headers" :rows="$rows" title="قائمة الموظفين" subtitle="تفاصيل العمل" />',
        ['headers' => $this->headers, 'rows' => $this->rows]
    );

    $view->assertSee('قائمة الموظفين');
    $view->assertSee('تفاصيل العمل');

    // Check headers
    $view->assertSee('الاسم');
    $view->assertSee('البريد الإلكتروني');
    $view->assertSee('الحالة');

    // Check row data
    $view->assertSee('أحمد علي');
    $view->assertSee('sara@nawwat.com');
    $view->assertSee('khaled@nawwat.com');
});

test('it renders status badge colors automatically based on values', function () {
    $view = $this->blade(
        '<x-table-wrapper :headers="$headers" :rows="$rows" />',
        ['headers' => $this->headers, 'rows' => $this->rows]
    );

    // Active -> erp-badge-success
    $view->assertSee('erp-badge-success');
    $view->assertSee('Active');

    // Pending -> erp-badge-warning
    $view->assertSee('erp-badge-warning');
    $view->assertSee('Pending');

    // Rejected -> erp-badge-danger
    $view->assertSee('erp-badge-danger');
    $view->assertSee('Rejected');
});

test('it shows loading shimmer placeholders when loading state is enabled', function () {
    $view = $this->blade(
        '<x-table-wrapper :headers="$headers" :rows="$rows" :loading="true" />',
        ['headers' => $this->headers, 'rows' => $this->rows]
    );

    // Shimmer rows should have animate-pulse
    $view->assertSee('animate-pulse');

    // Actual data should not be visible during loading
    $view->assertDontSee('أحمد علي');
});

test('it shows empty zero state graphic and localized message when empty', function () {
    app()->setLocale('ar');
    $view = $this->blade(
        '<x-table-wrapper :headers="$headers" :rows="[]" :empty="true" />',
        ['headers' => $this->headers]
    );

    $view->assertSee('لم يتم العثور على أي نتائج');
    $view->assertSee('لا توجد بيانات مطابقة لمعايير البحث والفلترة حالياً.');

    // In English
    app()->setLocale('en');
    $view = $this->blade(
        '<x-table-wrapper :headers="$headers" :rows="[]" :empty="true" />',
        ['headers' => $this->headers]
    );

    $view->assertSee('No entries found');
    $view->assertSee('There are no active records matching the selected configurations right now.');
});

test('it supports sorting parameters and dynamic carets', function () {
    // Sorted by name asc
    $view = $this->blade(
        '<x-table-wrapper :headers="$headers" :rows="$rows" sort-field="name" sort-direction="asc" />',
        ['headers' => $this->headers, 'rows' => $this->rows]
    );

    // Up arrow for ASC should be rendered
    $view->assertSee('d="M5 15l7-7 7 7"', false);

    // Sorted by email desc
    $view = $this->blade(
        '<x-table-wrapper :headers="$headers" :rows="$rows" sort-field="email" sort-direction="desc" />',
        ['headers' => $this->headers, 'rows' => $this->rows]
    );

    // Down arrow for DESC should be rendered
    $view->assertSee('d="M19 9l-7 7-7-7"', false);
});

test('it supports bulk action selectors and select all controls', function () {
    $view = $this->blade(
        '<x-table-wrapper :headers="$headers" :rows="$rows" :bulk-actions="true" />',
        ['headers' => $this->headers, 'rows' => $this->rows]
    );

    $view->assertSee('id="select-all-checkbox"', false);
    $view->assertSee('class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-slate-300 dark:border-slate-700 dark:bg-slate-950 cursor-pointer row-select-checkbox"', false);
    $view->assertSee('id="bulk-actions-panel"', false);
});

test('it renders export control buttons in both Arabic and English', function () {
    app()->setLocale('ar');
    $view = $this->blade(
        '<x-table-wrapper :headers="$headers" :rows="$rows" :export-actions="true" />',
        ['headers' => $this->headers, 'rows' => $this->rows]
    );
    $view->assertSee('تصدير البيانات');
    $view->assertSee('Excel (.xlsx)');

    app()->setLocale('en');
    $view = $this->blade(
        '<x-table-wrapper :headers="$headers" :rows="$rows" :export-actions="true" />',
        ['headers' => $this->headers, 'rows' => $this->rows]
    );
    $view->assertSee('Export');
});
