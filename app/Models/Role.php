<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['company_id', 'name', 'key', 'description'])]
class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the permissions assigned to this role.
     *
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }

    /**
     * Get the users assigned to this role.
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('company_id')
            ->withTimestamps();
    }
}
