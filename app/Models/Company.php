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
     * Get the job titles defined for this company.
     *
     * @return HasMany<JobTitle, $this>
     */
    public function jobTitles(): HasMany
    {
        return $this->hasMany(JobTitle::class);
    }

    /**
     * Get the employees assigned to this company.
     *
     * @return HasMany<Employee, $this>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get the employee documents owned by this company.
     *
     * @return HasMany<EmployeeDocument, $this>
     */
    public function employeeDocuments(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    /**
     * Get the attendance records owned by this company.
     *
     * @return HasMany<AttendanceRecord, $this>
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Get the leave types configured for this company.
     *
     * @return HasMany<LeaveType, $this>
     */
    public function leaveTypes(): HasMany
    {
        return $this->hasMany(LeaveType::class);
    }

    /**
     * Get the leave balances owned by this company.
     *
     * @return HasMany<LeaveBalance, $this>
     */
    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /**
     * Get the leave requests owned by this company.
     *
     * @return HasMany<LeaveRequest, $this>
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
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
