<?php

return [
    'module' => 'Human resources',
    'employees' => 'Employees',
    'departments' => 'Departments',
    'positions' => 'Positions',
    'attendance' => 'Attendance',
    'leave' => 'Leave',
    'payroll' => 'Payroll',
    'employee_statuses' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'on_leave' => 'On leave',
        'terminated' => 'Terminated',
    ],
    'department_statuses' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],
    'job_title_statuses' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],
    'genders' => [
        'male' => 'Male',
        'female' => 'Female',
    ],
    'work_types' => [
        'full_time' => 'Full time',
        'part_time' => 'Part time',
        'contract' => 'Contract',
        'remote' => 'Remote',
        'hybrid' => 'Hybrid',
    ],
    'fields' => [
        'employee_number' => 'Employee number',
        'job_title' => 'Job title',
        'department' => 'Department',
        'manager' => 'Manager',
        'hire_date' => 'Hire date',
        'employment_status' => 'Employment status',
    ],
    'metrics' => [
        'total_employees' => 'Total employees',
        'active_employees' => 'Active employees',
        'inactive_employees' => 'Inactive employees',
        'employees_by_department' => 'Employees by department',
        'employees_by_status' => 'Employees by status',
        'new_hires' => 'New hires',
        'documents_expiring_soon' => 'Documents expiring soon',
        'unassigned_department' => 'Unassigned',
    ],
    'import' => [
        'columns' => [
            'employee_number' => 'Employee number',
            'first_name_ar' => 'Arabic first name',
            'last_name_ar' => 'Arabic last name',
            'email' => 'Email',
            'phone' => 'Phone',
            'department' => 'Department',
            'job_title' => 'Job title',
            'hire_date' => 'Hire date',
            'basic_salary' => 'Basic salary',
        ],
    ],
];
