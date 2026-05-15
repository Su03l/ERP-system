<?php

namespace App\Http\Requests;

use App\Models\CompanyApiToken;
use App\Models\Permission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreApiTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', CompanyApiToken::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['sometimes', 'array'],
            'abilities.*' => ['string', 'max:150'],
            'expires_at' => ['sometimes', 'nullable', 'date', 'after:now'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $abilities = $this->input('abilities', []);

                if (! is_array($abilities) || in_array('*', $abilities, true)) {
                    return;
                }

                foreach ($abilities as $ability) {
                    if (! Permission::query()->where('key', $ability)->exists()) {
                        $validator->errors()->add('abilities', "The ability [{$ability}] is not registered.");
                    }
                }
            },
        ];
    }
}
