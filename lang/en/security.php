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
        ],
    ],
];
