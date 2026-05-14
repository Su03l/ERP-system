<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'customer_id' => $this->customer_id,
            'project_manager_id' => $this->project_manager_id,
            'workflow_instance_id' => $this->workflow_instance_id,
            'code' => $this->code,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'budget' => $this->budget,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'priority' => $this->priority?->value,
            'priority_label' => $this->priority?->label(),
            'progress_percentage' => $this->progress_percentage,
            'metadata' => $this->metadata,
            'customer' => $this->when($this->relationLoaded('customer') && $this->customer !== null, fn (): array => [
                'id' => $this->customer->id,
                'name_ar' => $this->customer->name_ar,
                'name_en' => $this->customer->name_en,
            ]),
            'project_manager' => $this->when($this->relationLoaded('projectManager') && $this->projectManager !== null, fn (): array => [
                'id' => $this->projectManager->id,
                'first_name_ar' => $this->projectManager->first_name_ar,
                'last_name_ar' => $this->projectManager->last_name_ar,
            ]),
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
        ];
    }
}
