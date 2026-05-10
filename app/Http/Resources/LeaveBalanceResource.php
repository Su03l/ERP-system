<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveBalanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'employee_id' => $this->employee_id,
            'leave_type_id' => $this->leave_type_id,
            'year' => $this->year,
            'opening_balance' => $this->opening_balance,
            'accrued_days' => $this->accrued_days,
            'used_days' => $this->used_days,
            'remaining_days' => $this->remaining_days,
            'metadata' => $this->metadata,
            'employee' => EmployeeResource::make($this->whenLoaded('employee')),
            'leave_type' => LeaveTypeResource::make($this->whenLoaded('leaveType')),
        ];
    }
}
