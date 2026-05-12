<?php

return [
    'cycle_types' => [
        'monthly' => 'شهري',
        'semi_monthly' => 'نصف شهري',
        'weekly' => 'أسبوعي',
    ],
    'salary_component_types' => [
        'allowance' => 'بدل',
        'deduction' => 'استقطاع',
    ],
    'salary_component_calculation_types' => [
        'fixed' => 'مبلغ ثابت',
        'percentage' => 'نسبة مئوية',
    ],
    'salary_component_statuses' => [
        'active' => 'نشط',
        'inactive' => 'غير نشط',
    ],
    'salary_package_statuses' => [
        'active' => 'نشط',
        'inactive' => 'غير نشط',
    ],
    'period_statuses' => [
        'draft' => 'مسودة',
        'open' => 'مفتوحة',
        'processing' => 'قيد المعالجة',
        'approved' => 'معتمدة',
        'paid' => 'مدفوعة',
        'closed' => 'مغلقة',
        'cancelled' => 'ملغاة',
    ],
    'run_statuses' => [
        'draft' => 'مسودة',
        'processing' => 'قيد المعالجة',
        'generated' => 'منشأة',
        'pending_approval' => 'بانتظار الاعتماد',
        'approved' => 'معتمدة',
        'rejected' => 'مرفوضة',
        'paid' => 'مدفوعة',
        'cancelled' => 'ملغاة',
    ],
    'run_item_statuses' => [
        'draft' => 'مسودة',
        'calculated' => 'محسوبة',
        'approved' => 'معتمدة',
        'paid' => 'مدفوعة',
        'cancelled' => 'ملغاة',
    ],
    'components' => [
        'housing_allowance_ar' => 'بدل السكن',
        'housing_allowance_en' => 'Housing allowance',
        'transportation_allowance_ar' => 'بدل النقل',
        'transportation_allowance_en' => 'Transportation allowance',
    ],
    'payslip' => [
        'employee' => 'الموظف',
        'period' => 'الفترة',
        'basic_salary' => 'الراتب الأساسي',
        'allowances' => 'البدلات',
        'deductions' => 'الاستقطاعات',
        'gross_salary' => 'إجمالي الراتب',
        'net_salary' => 'صافي الراتب',
    ],
];
