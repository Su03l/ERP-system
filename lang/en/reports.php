<?php

return [
    'attributes' => [
        'report_key' => 'report',
        'date_from' => 'date from',
        'date_to' => 'date to',
        'company_id' => 'company',
        'department_id' => 'department',
        'employee_id' => 'employee',
        'export_format' => 'export format',
        'locale' => 'locale',
    ],
    'validation' => [
        'company_scope' => 'The report cannot be executed for another company.',
        'unsupported_export' => 'The export format is not supported for this report.',
    ],
    'pdf' => [
        'package_missing' => 'The PDF service requires an approved PDF package before files can be generated.',
    ],
];
