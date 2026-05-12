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
    'validation' => [
        'journal_entries' => [
            'unbalanced' => 'يجب أن تتوازن القيود المحاسبية بين المدين والدائن قبل الترحيل.',
        ],
    ],
];
