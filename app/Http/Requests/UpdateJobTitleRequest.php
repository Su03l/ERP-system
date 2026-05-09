<?php

namespace App\Http\Requests;

use App\Enums\JobTitleStatus;
use App\Models\JobTitle;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobTitleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $jobTitle = $this->jobTitle();

        return $jobTitle instanceof JobTitle && ($this->user()?->can('update', $jobTitle) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['prohibited'],
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'code' => [
                'sometimes', 'nullable', 'string', 'max:50',
                Rule::unique('job_titles', 'code')->where('company_id', (int) ($this->user()?->company_id ?? 0))->ignore($this->jobTitle()?->id),
            ],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'required', Rule::enum(JobTitleStatus::class)],
        ];
    }

    private function jobTitle(): ?JobTitle
    {
        $jobTitle = $this->route('job_title') ?? $this->route('jobTitle');
        if ($jobTitle instanceof JobTitle) {
            return $jobTitle;
        }
        if (is_numeric($jobTitle)) {
            return JobTitle::query()->find((int) $jobTitle);
        }

        return null;
    }
}
