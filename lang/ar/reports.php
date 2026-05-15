<?php

return [
    'attributes' => [
        'report_key' => 'التقرير',
        'date_from' => 'من تاريخ',
        'date_to' => 'إلى تاريخ',
        'company_id' => 'الشركة',
        'department_id' => 'القسم',
        'employee_id' => 'الموظف',
        'export_format' => 'صيغة التصدير',
        'locale' => 'اللغة',
    ],
    'validation' => [
        'company_scope' => 'لا يمكن تنفيذ التقرير لشركة أخرى.',
        'unsupported_export' => 'صيغة التصدير غير مدعومة لهذا التقرير.',
    ],
    'pdf' => [
        'package_missing' => 'خدمة PDF تحتاج إلى حزمة PDF معتمدة قبل إنشاء الملفات.',
    ],
];
