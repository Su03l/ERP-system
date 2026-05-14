<?php

return [
    'lead_statuses' => [
        'new' => 'جديد',
        'contacted' => 'تم التواصل',
        'qualified' => 'مؤهل',
        'converted' => 'محول',
        'lost' => 'خاسر',
    ],
    'contact_statuses' => [
        'active' => 'نشط',
        'inactive' => 'غير نشط',
        'archived' => 'مؤرشف',
    ],
    'project_statuses' => [
        'draft' => 'مسودة',
        'pending_approval' => 'قيد الموافقة',
        'active' => 'نشط',
        'on_hold' => 'معلق',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغى',
    ],
    'project_task_statuses' => [
        'todo' => 'للعمل',
        'pending_approval' => 'قيد الموافقة',
        'in_progress' => 'قيد التنفيذ',
        'review' => 'مراجعة',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغى',
    ],
    'project_priorities' => [
        'low' => 'منخفضة',
        'medium' => 'متوسطة',
        'high' => 'عالية',
        'urgent' => 'عاجلة',
    ],
    'validation' => [
        'leads' => [
            'assigned_user_company' => 'يجب أن ينتمي المستخدم المسند إليه لنفس الشركة.',
        ],
        'contacts' => [
            'customer_company' => 'يجب أن ينتمي العميل لنفس الشركة.',
            'lead_company' => 'يجب أن ينتمي العميل المحتمل لنفس الشركة.',
        ],
    ],
    'metrics' => [
        'total_projects' => 'إجمالي المشاريع',
        'overdue_tasks' => 'المهام المتأخرة',
        'project_profitability' => 'ربحية المشروع',
        'total_logged_hours' => 'إجمالي الساعات المسجلة',
        'billable_hours' => 'الساعات القابلة للفوترة',
        'lead_conversion' => 'تحويل العملاء المحتملين',
    ],
];
