<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChartDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('analytics.view') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['line', 'bar', 'pie', 'donut', 'area'])],
            'labels' => ['sometimes', 'array'],
            'labels.*' => ['string', 'max:150'],
            'datasets' => ['sometimes', 'array'],
            'series' => ['sometimes', 'array'],
            'totals' => ['sometimes', 'array'],
            'metadata' => ['sometimes', 'array'],
        ];
    }
}
