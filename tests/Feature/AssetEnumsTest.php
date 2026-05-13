<?php

use App\Enums\AssetCategoryStatus;
use App\Enums\AssetDepreciationMethod;
use App\Enums\AssetMaintenanceStatus;
use App\Enums\AssetStatus;
use App\Enums\CustodyStatus;

it('provides stable values for asset management enums', function () {
    expect(AssetStatus::values())->toBe([
        'available',
        'assigned',
        'under_maintenance',
        'retired',
        'lost',
    ])
        ->and(AssetCategoryStatus::values())->toBe(['active', 'inactive'])
        ->and(AssetDepreciationMethod::values())->toBe([
            'straight_line',
            'declining_balance',
            'units_of_production',
        ])
        ->and(CustodyStatus::values())->toBe([
            'pending',
            'assigned',
            'returned',
            'rejected',
            'cancelled',
        ])
        ->and(AssetMaintenanceStatus::values())->toBe([
            'scheduled',
            'in_progress',
            'completed',
            'cancelled',
        ]);
});

it('provides Arabic labels for asset management enums by default locale', function () {
    app()->setLocale('ar');

    expect(AssetStatus::Lost->label())->toBe('مفقود')
        ->and(AssetCategoryStatus::Active->label())->toBe('نشط')
        ->and(AssetDepreciationMethod::StraightLine->label())->toBe('القسط الثابت')
        ->and(CustodyStatus::Returned->label())->toBe('مسترجع')
        ->and(AssetMaintenanceStatus::InProgress->label())->toBe('قيد التنفيذ');
});

it('provides English labels for asset management enums', function () {
    app()->setLocale('en');

    expect(AssetStatus::UnderMaintenance->label())->toBe('Under maintenance')
        ->and(AssetCategoryStatus::Inactive->label())->toBe('Inactive')
        ->and(AssetDepreciationMethod::UnitsOfProduction->label())->toBe('Units of production')
        ->and(CustodyStatus::Pending->label())->toBe('Pending')
        ->and(AssetMaintenanceStatus::Scheduled->label())->toBe('Scheduled');
});

it('provides option arrays for asset management enums', function () {
    app()->setLocale('en');

    expect(AssetStatus::options())->toContain([
        'value' => 'available',
        'label' => 'Available',
    ])
        ->and(AssetCategoryStatus::options())->toContain([
            'value' => 'active',
            'label' => 'Active',
        ])
        ->and(AssetDepreciationMethod::options())->toContain([
            'value' => 'straight_line',
            'label' => 'Straight line',
        ])
        ->and(CustodyStatus::options())->toContain([
            'value' => 'assigned',
            'label' => 'Assigned',
        ])
        ->and(AssetMaintenanceStatus::options())->toContain([
            'value' => 'completed',
            'label' => 'Completed',
        ]);
});
