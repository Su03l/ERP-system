<?php

return [
    'plan_statuses' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'archived' => 'Archived',
    ],
    'subscription_statuses' => [
        'trialing' => 'Trialing',
        'active' => 'Active',
        'past_due' => 'Past due',
        'grace' => 'Grace period',
        'cancelled' => 'Cancelled',
        'expired' => 'Expired',
    ],
    'subscription_billing_cycles' => [
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
    ],
    'billing_cycles' => [
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
    ],
    'limit_keys' => [
        'users' => 'users',
        'employees' => 'employees',
        'storage_mb' => 'storage',
        'modules' => 'modules',
        'api_access' => 'API access',
        'advanced_reports' => 'advanced reports',
        'marketplace' => 'marketplace',
    ],
    'limits' => [
        'allowed' => ':limit is available on the current plan.',
        'allowed_unlimited' => ':limit is unlimited on the current plan.',
        'denied' => ':limit is unavailable or exceeds the current plan limit.',
    ],
    'subscription_invoice_statuses' => [
        'draft' => 'Draft',
        'open' => 'Open',
        'paid' => 'Paid',
        'partially_paid' => 'Partially paid',
        'overdue' => 'Overdue',
        'cancelled' => 'Cancelled',
        'voided' => 'Voided',
    ],
    'subscription_invoices' => [
        'cannot_cancel_paid' => 'Paid subscription invoices cannot be cancelled.',
    ],
    'add_on_statuses' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'archived' => 'Archived',
    ],
    'company_add_on_statuses' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'cancelled' => 'Cancelled',
        'expired' => 'Expired',
    ],
];
