<?php

namespace App\Services;

use App\Enums\AttendanceSource;
use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AttendanceImportDefinition
{
    public const EntityKey = 'attendance_records';

    public const ModuleKey = 'attendance';

    /**
     * @return array{
     *     entity_type: string,
     *     module_key: string,
     *     columns: array<int, array{key: string, label: string, required: bool, aliases: array<int, string>}>
     * }
     */
    public function definition(): array
    {
        return [
            'entity_type' => self::EntityKey,
            'module_key' => self::ModuleKey,
            'columns' => $this->columns(),
        ];
    }

    /**
     * @return array<int, array{key: string, label: string, required: bool, aliases: array<int, string>}>
     */
    public function columns(): array
    {
        return [
            $this->column('employee_number', __('hr.attendance.import.columns.employee_number'), true, ['رقم الموظف', 'Employee Number']),
            $this->column('attendance_date', __('hr.attendance.import.columns.attendance_date'), true, ['تاريخ الحضور', 'Attendance Date']),
            $this->column('clock_in_at', __('hr.attendance.import.columns.clock_in_at'), false, ['وقت الحضور', 'Clock In At']),
            $this->column('clock_out_at', __('hr.attendance.import.columns.clock_out_at'), false, ['وقت الانصراف', 'Clock Out At']),
            $this->column('status', __('hr.attendance.import.columns.status'), true, ['الحالة', 'Status']),
            $this->column('source', __('hr.attendance.import.columns.source'), false, ['المصدر', 'Source']),
            $this->column('notes', __('hr.attendance.import.columns.notes'), false, ['ملاحظات', 'Notes']),
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(Company|int $company, bool $updateMode = false): array
    {
        $companyId = $company instanceof Company ? $company->id : $company;

        return [
            'employee_number' => ['required', 'string', 'max:50', $this->employeeNumberExistsRule($companyId)],
            'attendance_date' => ['required', 'date'],
            'clock_in_at' => ['nullable', 'date'],
            'clock_out_at' => ['nullable', 'date', 'after:clock_in_at'],
            'status' => ['required', Rule::enum(AttendanceStatus::class)],
            'source' => ['nullable', Rule::enum(AttendanceSource::class)],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{valid: bool, data: array<string, mixed>, errors: array<string, array<int, string>>}
     */
    public function validateRow(array $row, Company|int $company, bool $updateMode = false): array
    {
        $companyId = $company instanceof Company ? $company->id : $company;
        $validator = Validator::make($row, $this->rules($companyId, $updateMode));
        $validator->after(function ($validator) use ($companyId, $row, $updateMode): void {
            if ($updateMode || $validator->errors()->has('employee_number') || $validator->errors()->has('attendance_date')) {
                return;
            }

            $employeeId = $this->resolveEmployeeId((string) $row['employee_number'], $companyId);

            if ($employeeId === null) {
                return;
            }

            $exists = AttendanceRecord::query()
                ->where('company_id', $companyId)
                ->where('employee_id', $employeeId)
                ->whereDate('attendance_date', (string) $row['attendance_date'])
                ->exists();

            if ($exists) {
                $validator->errors()->add('attendance_date', __('validation.unique', ['attribute' => __('hr.attendance.fields.attendance_date')]));
            }
        });
        $valid = ! $validator->fails();

        return [
            'valid' => $valid,
            'data' => $valid ? $this->mapValidatedRow($validator->validated(), $companyId) : $row,
            'errors' => $validator->errors()->toArray(),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{entity_type: string, module_key: string, total_rows: int, preview_rows: array<int, array{row_number: int, valid: bool, data: array<string, mixed>, errors: array<string, array<int, string>>}>}
     */
    public function preview(array $rows, Company|int $company, int $limit = 10, bool $updateMode = false): array
    {
        return [
            'entity_type' => self::EntityKey,
            'module_key' => self::ModuleKey,
            'total_rows' => count($rows),
            'preview_rows' => collect($rows)
                ->take(max(0, $limit))
                ->map(function (array $row, int $index) use ($company, $updateMode): array {
                    $validatedRow = $this->validateRow($row, $company, $updateMode);

                    return [
                        'row_number' => $index + 1,
                        ...$validatedRow,
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    private function employeeNumberExistsRule(int $companyId): callable
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($companyId): void {
            if ($value === null || $value === '') {
                return;
            }

            if (! Employee::query()->where('company_id', $companyId)->where('employee_number', $value)->exists()) {
                $fail(__('validation.exists', ['attribute' => __('hr.attendance.import.columns.employee_number')]));
            }
        };
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function mapValidatedRow(array $row, int $companyId): array
    {
        return [
            'employee_id' => $this->resolveEmployeeId((string) $row['employee_number'], $companyId),
            'attendance_date' => $row['attendance_date'],
            'clock_in_at' => $row['clock_in_at'] ?? null,
            'clock_out_at' => $row['clock_out_at'] ?? null,
            'status' => $row['status'],
            'source' => $row['source'] ?? null,
            'notes' => $row['notes'] ?? null,
        ];
    }

    private function resolveEmployeeId(string $employeeNumber, int $companyId): ?int
    {
        return Employee::query()
            ->where('company_id', $companyId)
            ->where('employee_number', $employeeNumber)
            ->value('id');
    }

    /**
     * @param  array<int, string>  $aliases
     * @return array{key: string, label: string, required: bool, aliases: array<int, string>}
     */
    private function column(string $key, string $label, bool $required, array $aliases): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'required' => $required,
            'aliases' => $aliases,
        ];
    }
}
