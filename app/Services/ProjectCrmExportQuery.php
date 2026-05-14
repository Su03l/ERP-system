<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CrmContact;
use App\Models\CrmLead;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTimeLog;
use App\Support\TenantContext;

class ProjectCrmExportQuery
{
    public function __construct(private readonly TenantContext $tenantContext) {}

    /** @return array<int, array<string, mixed>> */
    public function projects(Company|int|null $company = null): array
    {
        return Project::query()->with(['customer', 'projectManager'])->forCompany($this->companyId($company))->latest('id')->get()
            ->map(fn (Project $project): array => [
                'code' => $project->code,
                'name_ar' => $project->name_ar,
                'name_en' => $project->name_en,
                'customer' => $project->customer?->name_ar,
                'project_manager' => trim(($project->projectManager?->first_name_ar ?? '').' '.($project->projectManager?->last_name_ar ?? '')),
                'status' => $project->status?->value,
                'status_label' => $project->status?->label(),
                'priority' => $project->priority?->value,
                'priority_label' => $project->priority?->label(),
                'progress_percentage' => $project->progress_percentage,
                'budget' => $project->budget,
            ])->all();
    }

    /** @return array<int, array<string, mixed>> */
    public function projectTasks(Company|int|null $company = null): array
    {
        return ProjectTask::query()->with(['project', 'assignedEmployee'])->forCompany($this->companyId($company))->latest('id')->get()
            ->map(fn (ProjectTask $task): array => [
                'project_code' => $task->project?->code,
                'task_code' => $task->task_code,
                'title_ar' => $task->title_ar,
                'title_en' => $task->title_en,
                'assigned_employee' => trim(($task->assignedEmployee?->first_name_ar ?? '').' '.($task->assignedEmployee?->last_name_ar ?? '')),
                'status' => $task->status?->value,
                'status_label' => $task->status?->label(),
                'priority' => $task->priority?->value,
                'priority_label' => $task->priority?->label(),
                'progress_percentage' => $task->progress_percentage,
            ])->all();
    }

    /** @return array<int, array<string, mixed>> */
    public function leads(Company|int|null $company = null): array
    {
        return CrmLead::query()->forCompany($this->companyId($company))->latest('id')->get()
            ->map(fn (CrmLead $lead): array => [
                'name_ar' => $lead->name_ar,
                'name_en' => $lead->name_en,
                'company_name' => $lead->company_name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'source' => $lead->source,
                'status' => $lead->status?->value,
                'status_label' => $lead->status?->label(),
                'expected_value' => $lead->expected_value,
            ])->all();
    }

    /** @return array<int, array<string, mixed>> */
    public function contacts(Company|int|null $company = null): array
    {
        return CrmContact::query()->with(['customer', 'lead'])->forCompany($this->companyId($company))->latest('id')->get()
            ->map(fn (CrmContact $contact): array => [
                'name_ar' => $contact->name_ar,
                'name_en' => $contact->name_en,
                'customer' => $contact->customer?->name_ar,
                'lead' => $contact->lead?->name_ar,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'position' => $contact->position,
                'status' => $contact->status?->value,
                'status_label' => $contact->status?->label(),
            ])->all();
    }

    /** @return array<int, array<string, mixed>> */
    public function timeLogs(Company|int|null $company = null): array
    {
        return ProjectTimeLog::query()->with(['project', 'projectTask', 'employee'])->forCompany($this->companyId($company))->latest('id')->get()
            ->map(fn (ProjectTimeLog $timeLog): array => [
                'project_code' => $timeLog->project?->code,
                'task_code' => $timeLog->projectTask?->task_code,
                'employee' => trim(($timeLog->employee?->first_name_ar ?? '').' '.($timeLog->employee?->last_name_ar ?? '')),
                'log_date' => $timeLog->log_date?->toDateString(),
                'start_time' => $timeLog->start_time,
                'end_time' => $timeLog->end_time,
                'total_minutes' => $timeLog->total_minutes,
                'is_billable' => $timeLog->is_billable,
            ])->all();
    }

    private function companyId(Company|int|null $company): int
    {
        return (int) ($company instanceof Company ? $company->id : ($company ?? $this->tenantContext->companyId() ?? 0));
    }
}
