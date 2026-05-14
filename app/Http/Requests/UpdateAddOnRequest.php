<?php

namespace App\Http\Requests;

use App\Enums\AddOnStatus;
use App\Models\AddOn;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateAddOnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('add_ons.update');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $addOn = $this->addOn();

        return [
            'company_id' => ['prohibited'],
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('add_ons', 'code')->ignore($addOn?->id)],
            'description_ar' => ['sometimes', 'nullable', 'string'],
            'description_en' => ['sometimes', 'nullable', 'string'],
            'category' => ['sometimes', 'nullable', 'string', 'max:255'],
            'price_monthly' => ['sometimes', 'required', 'numeric', 'min:0', 'decimal:0,2'],
            'price_yearly' => ['sometimes', 'nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'status' => ['sometimes', 'required', Rule::enum(AddOnStatus::class)],
            'feature_key' => ['sometimes', 'nullable', 'string', 'max:255'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    private function addOn(): ?AddOn
    {
        $addOn = $this->route('addOn') ?? $this->route('add_on');

        if ($addOn instanceof AddOn) {
            return $addOn;
        }

        return is_numeric($addOn) ? AddOn::query()->find((int) $addOn) : null;
    }
}
