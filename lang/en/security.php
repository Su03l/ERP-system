<?php

return [
    'audit' => [
        'columns' => [
            'created_at' => 'Created at',
            'user' => 'User',
            'action' => 'Action',
            'entity' => 'Entity',
            'entity_id' => 'Entity ID',
            'ip_address' => 'IP address',
        ],
        'actions' => [
            'api_token_created' => 'API token created',
            'api_token_revoked' => 'API token revoked',
            'security_settings_updated' => 'Security settings updated',
            'sensitive_export_requested' => 'Sensitive export requested',
            'sensitive_export_approval_required' => 'Sensitive export approval required',
            'webhook_endpoint_created' => 'Webhook endpoint created',
            'webhook_endpoint_updated' => 'Webhook endpoint updated',
            'webhook_endpoint_deleted' => 'Webhook endpoint deleted',
        ],
    ],
    'exports' => [
        'columns' => [
            'name' => 'Name',
            'abilities' => 'Abilities',
            'last_used_at' => 'Last used at',
            'expires_at' => 'Expires at',
            'revoked_at' => 'Revoked at',
            'event_name' => 'Event',
            'status' => 'Status',
            'response_status' => 'Response status',
            'attempt_count' => 'Attempts',
            'delivered_at' => 'Delivered at',
            'failed_at' => 'Failed at',
            'user_id' => 'User ID',
            'ip_address' => 'IP address',
            'last_activity_at' => 'Last activity at',
            'event' => 'Event',
            'company_id' => 'Company ID',
            'created_at' => 'Created at',
        ],
    ],
];
