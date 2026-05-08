<?php

return [
    'required' => 'The :attribute field is required.',
    'string' => 'The :attribute field must be a string.',
    'max' => [
        'string' => 'The :attribute field must not be greater than :max characters.',
    ],
    'size' => [
        'string' => 'The :attribute field must be :size characters.',
    ],
    'array' => 'The :attribute field must be an array.',
    'boolean' => 'The :attribute field must be true or false.',
    'timezone' => 'The :attribute field must be a valid timezone.',
    'custom' => [],
    'attributes' => [
        'name' => 'name',
        'email' => 'email',
        'password' => 'password',
        'preferred_locale' => 'preferred language',
        'company_id' => 'company',
        'locale' => 'language',
        'timezone' => 'timezone',
        'currency' => 'currency',
        'date_preference' => 'date preference',
        'working_days' => 'working days',
        'branding.logo_path' => 'logo path',
        'branding.primary_color' => 'primary color',
        'branding.secondary_color' => 'secondary color',
        'notification_preferences.email_enabled' => 'email notifications',
        'notification_preferences.database_enabled' => 'system notifications',
        'notification_preferences.sms_enabled' => 'SMS notifications',
    ],
];
