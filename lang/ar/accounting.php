<?php

return [
    'account_types' => [
        'asset' => 'أصل',
        'liability' => 'التزام',
        'equity' => 'حقوق ملكية',
        'revenue' => 'إيراد',
        'expense' => 'مصروف',
    ],
    'normal_balances' => [
        'debit' => 'مدين',
        'credit' => 'دائن',
    ],
    'journal_entry_statuses' => [
        'draft' => 'مسودة',
        'pending_approval' => 'بانتظار الاعتماد',
        'approved' => 'معتمد',
        'posted' => 'مرحل',
        'rejected' => 'مرفوض',
        'voided' => 'ملغى',
    ],
    'journal_entry_sources' => [
        'manual' => 'يدوي',
        'payroll' => 'الرواتب',
        'invoice' => 'فاتورة',
        'payment' => 'دفعة',
        'adjustment' => 'تسوية',
        'import' => 'استيراد',
    ],
    'invoice_statuses' => [
        'draft' => 'مسودة',
        'sent' => 'مرسلة',
        'partially_paid' => 'مدفوعة جزئيا',
        'paid' => 'مدفوعة',
        'overdue' => 'متأخرة',
        'cancelled' => 'ملغاة',
        'voided' => 'ملغاة محاسبيا',
    ],
    'payment_statuses' => [
        'draft' => 'مسودة',
        'pending' => 'قيد الانتظار',
        'completed' => 'مكتملة',
        'failed' => 'فاشلة',
        'cancelled' => 'ملغاة',
        'refunded' => 'مستردة',
    ],
    'customer_statuses' => [
        'active' => 'نشط',
        'inactive' => 'غير نشط',
        'blocked' => 'محظور',
    ],
    'vendor_statuses' => [
        'active' => 'نشط',
        'inactive' => 'غير نشط',
        'blocked' => 'محظور',
    ],
    'validation' => [
        'accounts' => [
            'parent_self' => 'لا يمكن أن يكون الحساب أصلا لنفسه.',
            'posted_lines' => 'لا يمكن أرشفة الحسابات التي تحتوي على قيود مرحلة.',
        ],
        'sales_invoices' => [
            'editable_status' => 'يمكن تعديل فواتير المبيعات المسودة وغير المرحلة فقط.',
            'issuable_status' => 'يمكن إصدار فواتير المبيعات المسودة فقط.',
            'cancelable_status' => 'يمكن إلغاء فواتير المبيعات غير المرحلة وغير المدفوعة فقط.',
        ],
        'purchase_invoices' => [
            'editable_status' => 'يمكن تعديل فواتير المشتريات المسودة وغير المرحلة فقط.',
            'approvable_status' => 'يمكن اعتماد فواتير المشتريات المسودة فقط.',
            'cancelable_status' => 'يمكن إلغاء فواتير المشتريات غير المرحلة وغير المدفوعة فقط.',
        ],
        'journal_entries' => [
            'unbalanced' => 'يجب أن تتوازن القيود المحاسبية بين المدين والدائن قبل الترحيل.',
            'single_side' => 'يجب أن يستخدم كل سطر في القيد إما مدين أو دائن فقط.',
            'non_zero_line' => 'يجب أن يحتوي كل سطر في القيد على مبلغ مدين أو دائن.',
            'postable_status' => 'يمكن ترحيل القيود المسودة أو المعتمدة فقط.',
            'approvable_status' => 'يمكن اعتماد القيود المسودة أو قيد الاعتماد فقط.',
            'editable_status' => 'يمكن تعديل القيود المسودة فقط.',
            'approval_required' => 'يجب اعتماد هذا القيد قبل الترحيل.',
            'reversible_status' => 'يمكن عكس القيود المرحلة فقط.',
            'rejectable_status' => 'يمكن رفض القيود المسودة أو قيد الاعتماد فقط.',
        ],
    ],
];
