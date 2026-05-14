<?php

namespace App\Services;

class ProjectCrmImportDefinitionService
{
    /** @return array<string, array<string, mixed>> */
    public function definitions(): array
    {
        return [
            'projects' => $this->definition(['code', 'name_ar', 'name_en', 'customer_code', 'project_manager_employee_number', 'start_date', 'end_date', 'budget', 'status', 'priority']),
            'project_tasks' => $this->definition(['project_code', 'task_code', 'parent_task_code', 'assigned_employee_number', 'title_ar', 'title_en', 'due_date', 'status', 'priority', 'progress_percentage']),
            'crm_leads' => $this->definition(['name_ar', 'name_en', 'company_name', 'email', 'phone', 'source', 'status', 'expected_value']),
            'crm_contacts' => $this->definition(['name_ar', 'name_en', 'customer_code', 'lead_email', 'email', 'phone', 'position', 'status']),
            'project_time_logs' => $this->definition(['project_code', 'task_code', 'employee_number', 'log_date', 'start_time', 'end_time', 'total_minutes', 'is_billable']),
        ];
    }

    /** @param array<int, string> $columns */
    private function definition(array $columns): array
    {
        return [
            'columns' => $columns,
            'validation' => [
                'placeholder' => true,
                'message' => 'Import row validation will use tenant-scoped Form Requests and actions before persistence.',
            ],
        ];
    }
}
