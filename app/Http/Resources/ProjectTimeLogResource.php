<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectTimeLogResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'project_id' => $this->project_id,
            'project_task_id' => $this->project_task_id,
            'employee_id' => $this->employee_id,
            'log_date' => $this->log_date?->toDateString(),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'total_minutes' => $this->total_minutes,
            'is_billable' => $this->is_billable,
            'notes_ar' => $this->notes_ar,
            'notes_en' => $this->notes_en,
            'metadata' => $this->metadata,
            'project' => ProjectResource::make($this->whenLoaded('project')),
            'project_task' => ProjectTaskResource::make($this->whenLoaded('projectTask')),
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
        ];
    }
}
