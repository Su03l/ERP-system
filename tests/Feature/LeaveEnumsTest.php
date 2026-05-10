<?php

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveTypeStatus;

test('leave type statuses expose stable values and localized labels', function () {
    app()->setLocale('en');

    expect(LeaveTypeStatus::values())->toBe(['active', 'inactive'])
        ->and(LeaveTypeStatus::Active->label())->toBe('Active')
        ->and(LeaveTypeStatus::Inactive->label())->toBe('Inactive')
        ->and(LeaveTypeStatus::options())->toContain([
            'value' => 'active',
            'label' => 'Active',
        ]);

    app()->setLocale('ar');

    expect(LeaveTypeStatus::Active->label())->toBe('نشط');
});

test('leave request statuses expose stable values and localized labels', function () {
    app()->setLocale('en');

    expect(LeaveRequestStatus::values())->toBe([
        'draft',
        'pending',
        'approved',
        'rejected',
        'cancelled',
        'returned',
    ])
        ->and(LeaveRequestStatus::Approved->label())->toBe('Approved')
        ->and(LeaveRequestStatus::Returned->label())->toBe('Returned')
        ->and(LeaveRequestStatus::options())->toContain([
            'value' => 'pending',
            'label' => 'Pending',
        ]);

    app()->setLocale('ar');

    expect(LeaveRequestStatus::Rejected->label())->toBe('مرفوض');
});
