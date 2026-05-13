<?php

return [
    'depreciation_methods' => [
        'straight_line' => 'القسط الثابت',
        'declining_balance' => 'الرصيد المتناقص',
        'units_of_production' => 'وحدات الإنتاج',
    ],
    'asset_category_statuses' => [
        'active' => 'نشط',
        'inactive' => 'غير نشط',
    ],
    'asset_statuses' => [
        'available' => 'متاح',
        'assigned' => 'مخصص',
        'under_maintenance' => 'تحت الصيانة',
        'retired' => 'مستبعد',
        'lost' => 'مفقود',
    ],
    'custody_statuses' => [
        'pending' => 'قيد الانتظار',
        'assigned' => 'مخصص',
        'returned' => 'مسترجع',
        'rejected' => 'مرفوض',
        'cancelled' => 'ملغى',
    ],
    'maintenance_statuses' => [
        'scheduled' => 'مجدولة',
        'in_progress' => 'قيد التنفيذ',
        'completed' => 'مكتملة',
        'cancelled' => 'ملغاة',
    ],
    'depreciation_schedule_statuses' => [
        'draft' => 'مسودة',
        'calculated' => 'محسوبة',
        'posted' => 'مرحلة',
    ],
    'validation' => [
        'asset_categories' => [
            'parent_self' => 'لا يمكن أن يكون تصنيف الأصل أصلا لنفسه.',
            'parent_company' => 'يجب أن يتبع تصنيف الأصل الأب لنفس الشركة.',
        ],
        'assets' => [
            'category_company' => 'يجب أن يتبع تصنيف الأصل لنفس الشركة.',
            'assigned_employee_company' => 'يجب أن يتبع الموظف المستلم لنفس الشركة.',
            'assigned_archive' => 'يجب استرجاع الأصل المخصص قبل أرشفته.',
        ],
        'asset_custodies' => [
            'asset_company' => 'يجب أن يتبع الأصل لنفس الشركة.',
            'employee_company' => 'يجب أن يتبع موظف العهدة لنفس الشركة.',
            'asset_unavailable' => 'يمكن تخصيص الأصول المتاحة فقط.',
            'asset_not_assigned' => 'يمكن استرجاع الأصول المخصصة فقط.',
        ],
        'depreciation_schedules' => [
            'asset_company' => 'يجب أن يتبع أصل جدول الإهلاك لنفس الشركة.',
        ],
    ],
];
