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
    'validation' => [
        'journal_entries' => [
            'unbalanced' => 'Journal entry debits and credits must balance before posting.',
        ],
    ],
];
