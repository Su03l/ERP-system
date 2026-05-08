<?php

namespace App\Support;

use App\Models\Company;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class TenantContext
{
    public function company(): ?Company
    {
        $user = $this->user();

        if (! $user instanceof User) {
            return null;
        }

        return $user->company;
    }

    public function companyId(): ?int
    {
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
