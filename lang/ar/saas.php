<?php

return [
    'plan_statuses' => [
        'active' => 'نشط',
        'inactive' => 'غير نشط',
        'archived' => 'مؤرشف',
    ],
    'subscription_statuses' => [
        'trialing' => 'تجريبي',
        'active' => 'نشط',
        'past_due' => 'متأخر الدفع',
        'grace' => 'فترة سماح',
        'cancelled' => 'ملغي',
        'expired' => 'منتهي',
    ],
    'subscription_billing_cycles' => [
        'monthly' => 'شهري',
        'yearly' => 'سنوي',
    ],
    'limit_keys' => [
        'users' => 'المستخدمين',
        'employees' => 'الموظفين',
        'storage_mb' => 'التخزين',
        'modules' => 'الوحدات',
        'api_access' => 'الوصول للواجهة البرمجية',
        'advanced_reports' => 'التقارير المتقدمة',
        'marketplace' => 'المتجر',
    ],
    'limits' => [
        'allowed' => 'الميزة :limit متاحة في الخطة الحالية.',
        'allowed_unlimited' => 'الميزة :limit غير محدودة في الخطة الحالية.',
        'denied' => 'الميزة :limit غير متاحة أو تجاوزت حد الخطة الحالية.',
    ],
    'subscription_invoice_statuses' => [
        'draft' => 'مسودة',
        'open' => 'مفتوحة',
        'paid' => 'مدفوعة',
        'partially_paid' => 'مدفوعة جزئياً',
        'overdue' => 'متأخرة',
        'cancelled' => 'ملغاة',
        'voided' => 'باطلة',
    ],
    'subscription_invoices' => [
        'cannot_cancel_paid' => 'لا يمكن إلغاء فواتير الاشتراك المدفوعة.',
    ],
    'add_on_statuses' => [
        'active' => 'نشطة',
        'inactive' => 'غير نشطة',
        'archived' => 'مؤرشفة',
    ],
    'company_add_on_statuses' => [
        'active' => 'نشطة',
        'inactive' => 'غير نشطة',
        'cancelled' => 'ملغاة',
        'expired' => 'منتهية',
    ],
];
