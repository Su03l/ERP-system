<?php

use App\Services\KpiRegistry;
use App\Services\Kpis\Hr\TotalEmployeesKpi;

it('exposes KPI definitions with resolver metadata', function () {
    $registry = new KpiRegistry([app(TotalEmployeesKpi::class)]);

    $definition = $registry->available()[0];

    expect($definition['key'])->toBe('hr.total_employees')
        ->and($definition['module'])->toBe('hr')
        ->and($definition['label_ar'])->toBe('إجمالي الموظفين')
        ->and($definition['label_en'])->toBe('Total employees')
        ->and($definition['required_permission'])->toBe('employees.view')
        ->and($definition['resolver_class'])->toBe(TotalEmployeesKpi::class)
        ->and($definition['supports_date_range'])->toBeFalse()
        ->and($definition['default_date_range'])->toBeNull();
});

it('provides a default centralized KPI registry', function () {
    $keys = collect(KpiRegistry::default()->available())->pluck('key');

    expect($keys)->toContain('hr.total_employees')
        ->and($keys)->toContain('attendance.attendance_rate')
        ->and($keys)->toContain('leave.balance_summary');
});
