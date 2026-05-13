<?php

namespace App\Http\Requests;

use App\Enums\ContactStatus;
use App\Models\CrmContact;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCrmContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        $contact = $this->routeContact();

        return $contact !== null
            && $this->user()?->company_id !== null
            && $this->user()->company_id === $contact->company_id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->companyId();

        return [
            'company_id' => ['prohibited'],
            'customer_id' => ['sometimes', 'nullable', 'integer', Rule::exists('customers', 'id')->where('company_id', $companyId)],
            'lead_id' => ['sometimes', 'nullable', 'integer', Rule::exists('crm_leads', 'id')->where('company_id', $companyId)],
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:255'],
            'position' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes_ar' => ['sometimes', 'nullable', 'string'],
            'notes_en' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'required', Rule::enum(ContactStatus::class)],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    private function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }

    private function routeContact(): ?CrmContact
    {
        $contact = $this->route('crm_contact') ?? $this->route('contact');

        if ($contact instanceof CrmContact) {
            return $contact;
        }

        return $contact === null ? null : CrmContact::query()->find($contact);
    }
}
