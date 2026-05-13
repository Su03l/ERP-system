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
    'validation' => [
        'asset_categories' => [
            'parent_self' => 'لا يمكن أن يكون تصنيف الأصل أصلا لنفسه.',
            'parent_company' => 'يجب أن يتبع تصنيف الأصل الأب لنفس الشركة.',
        ],
    ],
];
