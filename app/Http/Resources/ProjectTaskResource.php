<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectTaskResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'project_id' => $this->project_id,
            'assigned_employee_id' => $this->assigned_employee_id,
            'parent_task_id' => $this->parent_task_id,
            'workflow_instance_id' => $this->workflow_instance_id,
            'task_code' => $this->task_code,
            'title_ar' => $this->title_ar,
            'title_en' => $this->title_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'start_date' => $this->start_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'completed_at' => $this->completed_at?->toJSON(),
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'priority' => $this->priority?->value,
            'priority_label' => $this->priority?->label(),
            'estimated_hours' => $this->estimated_hours,
            'actual_hours' => $this->actual_hours,
            'progress_percentage' => $this->progress_percentage,
            'metadata' => $this->metadata,
            'project' => ProjectResource::make($this->whenLoaded('project')),
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
        ];
    }
}
