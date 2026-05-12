<?php

namespace App\Http\Requests;

use App\Enums\AccountNormalBalance;
use App\Enums\AccountType;
use App\Models\Account;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $account = $this->routeAccount();

        return $account !== null
            && $this->user()?->company_id !== null
            && $this->user()->company_id === $account->company_id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->companyId();
        $account = $this->routeAccount();

        return [
            'company_id' => ['prohibited'],
            'parent_id' => ['sometimes', 'nullable', 'integer', Rule::exists('accounts', 'id')->where('company_id', $companyId)],
            'code' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('accounts', 'code')->where('company_id', $companyId)->ignore($account?->id)],
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'type' => ['sometimes', 'required', Rule::enum(AccountType::class)],
            'normal_balance' => ['sometimes', 'required', Rule::enum(AccountNormalBalance::class)],
            'level' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
            'is_system' => ['sometimes', 'boolean'],
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
                $account = $this->routeAccount();
                $parentId = $this->integer('parent_id') ?: null;

                if ($account === null || $parentId === null) {
                    return;
                }

                if ($parentId === $account->id) {
                    $validator->errors()->add('parent_id', __('accounting.validation.accounts.parent_self'));
                }
            },
        ];
    }

    private function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }

    private function routeAccount(): ?Account
    {
        $account = $this->route('account');

        if ($account instanceof Account) {
            return $account;
        }

        return $account === null ? null : Account::query()->find($account);
    }
}
