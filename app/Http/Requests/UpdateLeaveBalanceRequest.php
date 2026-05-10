<?php

namespace App\Http\Requests;

use App\Models\LeaveBalance;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $leaveBalance = $this->route('leave_balance') ?? $this->route('leaveBalance');
        $leaveBalance = $leaveBalance instanceof LeaveBalance ? $leaveBalance : LeaveBalance::query()->find($leaveBalance);

        return $leaveBalance instanceof LeaveBalance && ($this->user()?->can('update', $leaveBalance) ?? false);
    }

    public function rules(): array
    {
        return [
            'opening_balance' => ['sometimes', 'numeric'],
            'accrued_days' => ['sometimes', 'numeric'],
            'used_days' => ['sometimes', 'numeric'],
            'remaining_days' => ['sometimes', 'numeric'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
