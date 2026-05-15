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
];
