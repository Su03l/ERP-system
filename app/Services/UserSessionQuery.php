<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSession;

class UserSessionQuery
{
    /** @return array<int, array<string, mixed>> */
    public function rows(User $actor): array
    {
        return UserSession::query()
            ->where('company_id', $actor->company_id)
            ->with('user')
            ->latest('last_activity_at')
            ->get()
            ->map(fn (UserSession $session): array => [
                'id' => $session->id,
                'user_id' => $session->user_id,
                'user_name' => $session->user?->name,
                'ip_address' => $session->ip_address,
                'last_activity_at' => $session->last_activity_at?->toDateTimeString(),
                'revoked_at' => $session->revoked_at?->toDateTimeString(),
            ])
            ->all();
    }
}
