<?php

namespace App\Actions;

use App\Models\User;
use App\Models\UserSession;
use App\Services\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;

class RevokeUserSession
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(UserSession $session, User $actor): UserSession
    {
        if ($actor->company_id === null || $actor->company_id !== $session->company_id || ! $actor->hasPermission('user_sessions.revoke', $actor->company_id)) {
            throw new AuthorizationException('You are not authorized to revoke this session.');
        }

        $session->update(['revoked_at' => now()]);
        $this->auditLogger->log('user_session.revoked', $session, user: $actor, company: $session->company_id);

        return $session->refresh();
    }
}
