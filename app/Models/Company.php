<?php

namespace App\Models;

use App\Enums\CompanyModule;
use App\Services\CompanyModuleService;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'legal_name',
    'email',
    'phone',
    'status',
    'subdomain',
    'locale',
    'timezone',
    'currency',
    'settings',
])]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the users assigned to this company.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the roles defined for this company.
     *
     * @return HasMany<Role, $this>
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    /**
     * Get the departments defined for this company.
     *
     * @return HasMany<Department, $this>
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * @return HasMany<Workflow, $this>
     */
    public function workflows(): HasMany
    {
        return $this->hasMany(Workflow::class);
    }

    /**
     * @return HasMany<ImportJob, $this>
     */
    public function importJobs(): HasMany
    {
        return $this->hasMany(ImportJob::class);
    }

    /**
     * @return HasMany<ExportJob, $this>
     */
    public function exportJobs(): HasMany
    {
        return $this->hasMany(ExportJob::class);
    }

    /**
     * @return HasMany<MigrationSession, $this>
     */
    public function migrationSessions(): HasMany
    {
        return $this->hasMany(MigrationSession::class);
    }

    public function hasModule(CompanyModule|string $module): bool
    {
        return app(CompanyModuleService::class)->isEnabled($this, $module);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }
}
