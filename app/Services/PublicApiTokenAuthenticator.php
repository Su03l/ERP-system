<?php

namespace App\Services;

use App\Models\CompanyApiToken;
use Illuminate\Http\Request;

class PublicApiTokenAuthenticator
{
    public function tokenFromRequest(Request $request): ?CompanyApiToken
    {
        $plainToken = $request->bearerToken();

        if (! is_string($plainToken) || $plainToken === '') {
            return null;
        }

        return CompanyApiToken::query()
            ->with('company')
            ->where('token', hash('sha256', $plainToken))
            ->first();
    }

    public function isValid(CompanyApiToken $token): bool
    {
        if ($token->revoked_at !== null) {
            return false;
        }

        if ($token->expires_at !== null && $token->expires_at->isPast()) {
            return false;
        }

        return $token->company !== null;
    }

    public function touch(CompanyApiToken $token): void
    {
        $token->forceFill(['last_used_at' => now()])->save();
    }
}
