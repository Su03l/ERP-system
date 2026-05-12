<?php

return [
    'account_types' => [
        'asset' => 'Asset',
        'liability' => 'Liability',
        'equity' => 'Equity',
        'revenue' => 'Revenue',
        'expense' => 'Expense',
    ],
    'normal_balances' => [
        'debit' => 'Debit',
        'credit' => 'Credit',
    ],
    'journal_entry_statuses' => [
        'draft' => 'Draft',
        'pending_approval' => 'Pending approval',
        'approved' => 'Approved',
        'posted' => 'Posted',
        'rejected' => 'Rejected',
        'voided' => 'Voided',
    ],
    'journal_entry_sources' => [
        'manual' => 'Manual',
        'payroll' => 'Payroll',
        'invoice' => 'Invoice',
        'payment' => 'Payment',
        'adjustment' => 'Adjustment',
        'import' => 'Import',
    ],
    'invoice_statuses' => [
        'draft' => 'Draft',
        'sent' => 'Sent',
        'partially_paid' => 'Partially paid',
        'paid' => 'Paid',
        'overdue' => 'Overdue',
        'cancelled' => 'Cancelled',
        'voided' => 'Voided',
    ],
    'payment_statuses' => [
        'draft' => 'Draft',
        'pending' => 'Pending',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
    ],
    'customer_statuses' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'blocked' => 'Blocked',
    ],
    'validation' => [
        'accounts' => [
            'parent_self' => 'An account cannot be its own parent.',
            'posted_lines' => 'Accounts with posted journal entry lines cannot be archived.',
        ],
        'sales_invoices' => [
            'editable_status' => 'Only draft, unposted sales invoices can be edited.',
            'issuable_status' => 'Only draft sales invoices can be issued.',
            'cancelable_status' => 'Only unposted and unpaid sales invoices can be cancelled.',
        ],
        'journal_entries' => [
            'unbalanced' => 'Journal entry debits and credits must balance before posting.',
            'single_side' => 'Each journal entry line must use either debit or credit, not both.',
            'non_zero_line' => 'Each journal entry line must have a debit or credit amount.',
            'postable_status' => 'Only draft or approved journal entries can be posted.',
            'approvable_status' => 'Only draft or pending journal entries can be approved.',
            'editable_status' => 'Only draft journal entries can be edited.',
            'approval_required' => 'This journal entry must be approved before posting.',
            'reversible_status' => 'Only posted journal entries can be reversed.',
            'rejectable_status' => 'Only draft or pending journal entries can be rejected.',
        ],
    ],
];
