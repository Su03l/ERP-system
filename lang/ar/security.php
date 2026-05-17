<?php

return [
    'audit' => [
        'columns' => [
            'created_at' => 'وقت العملية',
            'user' => 'المستخدم',
            'action' => 'الإجراء',
            'entity' => 'الكيان',
            'entity_id' => 'رقم الكيان',
            'ip_address' => 'عنوان IP',
        ],
        'actions' => [
            'api_token_created' => 'إنشاء رمز API',
            'api_token_revoked' => 'إلغاء رمز API',
        ],
    ],
    'exports' => [
        'columns' => [
            'name' => 'الاسم',
            'abilities' => 'الصلاحيات',
            'last_used_at' => 'آخر استخدام',
            'expires_at' => 'تاريخ الانتهاء',
            'revoked_at' => 'تاريخ الإلغاء',
            'event_name' => 'الحدث',
            'status' => 'الحالة',
            'response_status' => 'حالة الاستجابة',
            'attempt_count' => 'عدد المحاولات',
            'delivered_at' => 'تاريخ الإرسال',
            'failed_at' => 'تاريخ الفشل',
            'user_id' => 'رقم المستخدم',
            'ip_address' => 'عنوان IP',
            'last_activity_at' => 'آخر نشاط',
            'event' => 'الحدث',
            'company_id' => 'رقم الشركة',
            'created_at' => 'تاريخ الإنشاء',
        ],
    ],
];
