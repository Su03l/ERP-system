<?php

namespace App\Support;

use App\Models\Company;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantContext
{
    public function __construct(private readonly Request $request) {}

    public function company(): ?Company
    {
        $company = $this->request->attributes->get('company');

        if ($company instanceof Company) {
            return $company;
        }

        $user = $this->user();

        if (! $user instanceof User) {
            return null;
        }

        return $user->company;
    }

    public function companyId(): ?int
    {
        $companyId = $this->request->attributes->get('company_id');

        if (is_numeric($companyId)) {
            return (int) $companyId;
        }

        $user = $this->user();

        if (! $user instanceof User) {
            return null;
        }

        return $user->company_id;
    }

    private function user(): ?Authenticatable
    {
        return Auth::user();
    }
}
