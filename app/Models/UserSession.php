<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\UserSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['company_id', 'user_id', 'session_id', 'ip_address', 'user_agent', 'last_activity_at', 'revoked_at', 'metadata'])]
class UserSession extends Model
{
    /** @use HasFactory<UserSessionFactory> */
    use BelongsToCompany, HasFactory;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'last_activity_at' => 'datetime',
            'revoked_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
