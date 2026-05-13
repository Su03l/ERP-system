<?php

return [
    'depreciation_methods' => [
        'straight_line' => 'Straight line',
        'declining_balance' => 'Declining balance',
        'units_of_production' => 'Units of production',
    ],
    'asset_category_statuses' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],
    'asset_statuses' => [
        'available' => 'Available',
        'assigned' => 'Assigned',
        'under_maintenance' => 'Under maintenance',
        'retired' => 'Retired',
        'lost' => 'Lost',
    ],
    'custody_statuses' => [
        'pending' => 'Pending',
        'assigned' => 'Assigned',
        'returned' => 'Returned',
        'rejected' => 'Rejected',
        'cancelled' => 'Cancelled',
    ],
    'maintenance_statuses' => [
        'scheduled' => 'Scheduled',
        'in_progress' => 'In progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],
    'validation' => [
        'asset_categories' => [
            'parent_self' => 'An asset category cannot be its own parent.',
            'parent_company' => 'The parent asset category must belong to the same company.',
        ],
        'assets' => [
            'category_company' => 'The asset category must belong to the same company.',
            'assigned_employee_company' => 'The assigned employee must belong to the same company.',
        ],
    ],
];
