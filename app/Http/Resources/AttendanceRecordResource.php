<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'employee_id' => $this->employee_id,
            'attendance_date' => $this->attendance_date?->toDateString(),
            'clock_in_at' => $this->clock_in_at?->toJSON(),
            'clock_out_at' => $this->clock_out_at?->toJSON(),
            'clock_in_ip' => $this->clock_in_ip,
            'clock_out_ip' => $this->clock_out_ip,
            'status' => $this->status?->value,
            'source' => $this->source?->value,
            'late_minutes' => $this->late_minutes,
            'overtime_minutes' => $this->overtime_minutes,
            'total_work_minutes' => $this->total_work_minutes,
            'notes' => $this->notes,
            'metadata' => $this->metadata,
            'employee' => EmployeeResource::make($this->whenLoaded('employee')),
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
        ];
    }
}
