<?php

namespace App\Http\Requests;

use App\Enums\AddOnStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class IndexAddOnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('add_ons.view');
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(AddOnStatus::class)],
            'category' => ['nullable', 'string', 'max:255'],
            'feature_key' => ['nullable', 'string', 'max:255'],
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }
}
