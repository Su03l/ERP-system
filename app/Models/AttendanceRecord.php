<?php

namespace App\Models;

use App\Enums\AttendanceSource;
use App\Enums\AttendanceStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\AttendanceRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'employee_id',
    'attendance_date',
    'clock_in_at',
    'clock_out_at',
    'clock_in_ip',
    'clock_out_ip',
    'status',
    'source',
    'late_minutes',
    'overtime_minutes',
    'total_work_minutes',
    'notes',
    'metadata',
])]
class AttendanceRecord extends Model
{
    /** @use HasFactory<AttendanceRecordFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the employee this attendance record belongs to.
     *
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'clock_in_at' => 'datetime',
            'clock_out_at' => 'datetime',
            'status' => AttendanceStatus::class,
            'source' => AttendanceSource::class,
            'late_minutes' => 'integer',
            'overtime_minutes' => 'integer',
            'total_work_minutes' => 'integer',
            'metadata' => 'array',
        ];
    }
}
