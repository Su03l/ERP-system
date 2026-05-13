<?php

return [
    'lead_statuses' => [
        'new' => 'New',
        'contacted' => 'Contacted',
        'qualified' => 'Qualified',
        'converted' => 'Converted',
        'lost' => 'Lost',
    ],
    'contact_statuses' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'archived' => 'Archived',
    ],
    'project_statuses' => [
        'draft' => 'Draft',
        'pending_approval' => 'Pending approval',
        'active' => 'Active',
        'on_hold' => 'On hold',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],
    'project_task_statuses' => [
        'todo' => 'To do',
        'pending_approval' => 'Pending approval',
        'in_progress' => 'In progress',
        'review' => 'Review',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],
    'project_priorities' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ],
    'validation' => [
        'leads' => [
            'assigned_user_company' => 'The assigned user must belong to the same company.',
        ],
        'contacts' => [
            'customer_company' => 'The customer must belong to the same company.',
            'lead_company' => 'The lead must belong to the same company.',
        ],
    ],
];
