<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'user_id' => $this->user_id,
            'department_id' => $this->department_id,
            'job_title_id' => $this->job_title_id,
            'manager_id' => $this->manager_id,
            'employee_number' => $this->employee_number,
            'first_name_ar' => $this->first_name_ar,
            'last_name_ar' => $this->last_name_ar,
            'first_name_en' => $this->first_name_en,
            'last_name_en' => $this->last_name_en,
            'email' => $this->email,
            'phone' => $this->phone,
            'national_id' => $this->national_id,
            'nationality' => $this->nationality,
            'gender' => $this->gender?->value,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'hire_date' => $this->hire_date?->toDateString(),
            'employment_status' => $this->employment_status?->value,
            'work_type' => $this->work_type?->value,
            'basic_salary' => $this->when($request->user()?->can('viewSalary', $this->resource), $this->basic_salary),
            'department' => DepartmentResource::make($this->whenLoaded('department')),
            'job_title' => JobTitleResource::make($this->whenLoaded('jobTitle')),
            'manager' => EmployeeResource::make($this->whenLoaded('manager')),
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
        ];
    }
}
