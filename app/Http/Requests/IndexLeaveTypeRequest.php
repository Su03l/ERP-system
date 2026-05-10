<?php

namespace App\Http\Requests;

use App\Enums\LeaveTypeStatus;
use App\Models\LeaveType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', LeaveType::class) ?? false;
    }

    public function rules(): array
    {
        return ['status' => ['sometimes', 'nullable', Rule::enum(LeaveTypeStatus::class)], 'per_page' => ['sometimes', 'integer', 'min:1', 'max:100']];
    }
}
