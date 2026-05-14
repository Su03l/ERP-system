<?php

namespace App\Http\Requests;

use App\Enums\PlanStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class IndexPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('plans.view');
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(PlanStatus::class)],
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }
}
