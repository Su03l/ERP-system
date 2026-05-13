<?php

namespace App\Http\Requests;

use App\Enums\LeadStatus;
use App\Models\CrmLead;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCrmLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $lead = $this->routeLead();

        return $lead !== null
            && $this->user()?->company_id !== null
            && $this->user()->company_id === $lead->company_id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->companyId();

        return [
            'company_id' => ['prohibited'],
            'assigned_user_id' => ['sometimes', 'nullable', 'integer', Rule::exists('users', 'id')->where('company_id', $companyId)],
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'company_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:255'],
            'source' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'required', Rule::enum(LeadStatus::class)],
            'expected_value' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'notes_ar' => ['sometimes', 'nullable', 'string'],
            'notes_en' => ['sometimes', 'nullable', 'string'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    private function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }

    private function routeLead(): ?CrmLead
    {
        $lead = $this->route('crm_lead') ?? $this->route('lead');

        if ($lead instanceof CrmLead) {
            return $lead;
        }

        return $lead === null ? null : CrmLead::query()->find($lead);
    }
}
