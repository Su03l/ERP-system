<?php

namespace App\Models;

use App\Enums\EmployeeStatus;
use App\Enums\Gender;
use App\Enums\WorkType;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id',
    'user_id',
    'department_id',
    'job_title_id',
    'manager_id',
    'employee_number',
    'first_name_ar',
    'last_name_ar',
    'first_name_en',
    'last_name_en',
    'email',
    'phone',
    'national_id',
    'nationality',
    'gender',
    'date_of_birth',
    'hire_date',
    'employment_status',
    'work_type',
    'basic_salary',
])]
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use BelongsToCompany, HasFactory, SoftDeletes;

    /**
     * Get the user account linked to this employee.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department this employee belongs to.
     *
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the job title assigned to this employee.
     *
     * @return BelongsTo<JobTitle, $this>
     */
    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class);
    }

    /**
     * Get the employee's manager.
     *
     * @return BelongsTo<Employee, $this>
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    /**
     * Get employees managed by this employee.
     *
     * @return HasMany<Employee, $this>
     */
    public function directReports(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    /**
     * Get the documents linked to this employee.
     *
     * @return HasMany<EmployeeDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    /**
     * Get the attendance records linked to this employee.
     *
     * @return HasMany<AttendanceRecord, $this>
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Get the leave balances tracked for this employee.
     *
     * @return HasMany<LeaveBalance, $this>
     */
    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /**
     * Get the leave requests submitted for this employee.
     *
     * @return HasMany<LeaveRequest, $this>
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get the salary packages linked to this employee.
     *
     * @return HasMany<EmployeeSalaryPackage, $this>
     */
    public function salaryPackages(): HasMany
    {
        return $this->hasMany(EmployeeSalaryPackage::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'hire_date' => 'date',
            'employment_status' => EmployeeStatus::class,
            'gender' => Gender::class,
            'work_type' => WorkType::class,
            'basic_salary' => 'decimal:2',
        ];
    }
}
