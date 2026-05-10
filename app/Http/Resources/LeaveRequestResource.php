<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'employee_id' => $this->employee_id,
            'leave_type_id' => $this->leave_type_id,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'total_days' => $this->total_days,
            'reason' => $this->reason,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'workflow_instance_id' => $this->workflow_instance_id,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->toJSON(),
            'rejected_reason' => $this->rejected_reason,
            'metadata' => $this->metadata,
            'employee' => EmployeeResource::make($this->whenLoaded('employee')),
            'leave_type' => LeaveTypeResource::make($this->whenLoaded('leaveType')),
        ];
    }
}
