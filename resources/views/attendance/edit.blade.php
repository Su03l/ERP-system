@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'تعديل سجل حضور وانصراف' : 'Edit Attendance Record')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                {{ app()->getLocale() === 'ar' ? 'تعديل سجل حضور وانصراف' : 'Edit Attendance Record' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ app()->getLocale() === 'ar' ? 'قم بتعديل بيانات الحضور والانصراف للموظف.' : 'Edit employee attendance record details.' }}
            </p>
        </div>
        <a href="{{ route('attendance-records.index') }}" class="btn-secondary px-4 py-2 text-sm">
            {{ app()->getLocale() === 'ar' ? 'عودة للسجلات' : 'Back to Records' }}
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
        <form action="{{ route('attendance-records.update', $attendanceRecord->id) }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 text-sm">
                    <ul class="list-disc px-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Employee -->
                <div class="sm:col-span-2">
                    <label for="employee_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ app()->getLocale() === 'ar' ? 'الموظف' : 'Employee' }} <span class="text-rose-500">*</span>
                    </label>
                    <select name="employee_id" id="employee_id" required
                        class="erp-input w-full">
                        <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر الموظف --' : '-- Select Employee --' }}</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id', $attendanceRecord->employee_id) == $employee->id ? 'selected' : '' }}>
                                {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_number }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date -->
                <div>
                    <label for="attendance_date" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ app()->getLocale() === 'ar' ? 'تاريخ السجل' : 'Attendance Date' }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="attendance_date" id="attendance_date" value="{{ old('attendance_date', $attendanceRecord->attendance_date?->format('Y-m-d')) }}" required
                        class="erp-input w-full">
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }} <span class="text-rose-500">*</span>
                    </label>
                    <select name="status" id="status" required class="erp-input w-full">
                        <option value="present" {{ old('status', $attendanceRecord->status?->value) === 'present' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'حاضر' : 'Present' }}</option>
                        <option value="absent" {{ old('status', $attendanceRecord->status?->value) === 'absent' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'غائب' : 'Absent' }}</option>
                        <option value="late" {{ old('status', $attendanceRecord->status?->value) === 'late' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'متأخر' : 'Late' }}</option>
                        <option value="on_leave" {{ old('status', $attendanceRecord->status?->value) === 'on_leave' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'في إجازة' : 'On Leave' }}</option>
                    </select>
                </div>

                <!-- Clock In -->
                <div>
                    <label for="clock_in_at" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ app()->getLocale() === 'ar' ? 'وقت الحضور' : 'Clock In At' }}
                    </label>
                    <input type="datetime-local" name="clock_in_at" id="clock_in_at" value="{{ old('clock_in_at', $attendanceRecord->clock_in_at?->format('Y-m-d\TH:i')) }}"
                        class="erp-input w-full">
                </div>

                <!-- Clock Out -->
                <div>
                    <label for="clock_out_at" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ app()->getLocale() === 'ar' ? 'وقت الانصراف' : 'Clock Out At' }}
                    </label>
                    <input type="datetime-local" name="clock_out_at" id="clock_out_at" value="{{ old('clock_out_at', $attendanceRecord->clock_out_at?->format('Y-m-d\TH:i')) }}"
                        class="erp-input w-full">
                </div>
                
                <!-- Source -->
                <div class="sm:col-span-2">
                    <label for="source" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ app()->getLocale() === 'ar' ? 'مصدر التسجيل' : 'Entry Source' }}
                    </label>
                    <select name="source" id="source" class="erp-input w-full">
                        <option value="manual" {{ old('source', $attendanceRecord->source?->value) === 'manual' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'إدخال يدوي' : 'Manual' }}</option>
                        <option value="web" {{ old('source', $attendanceRecord->source?->value) === 'web' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'البوابة' : 'Web' }}</option>
                        <option value="mobile" {{ old('source', $attendanceRecord->source?->value) === 'mobile' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'الجوال' : 'Mobile' }}</option>
                        <option value="device" {{ old('source', $attendanceRecord->source?->value) === 'device' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'جهاز بصمة' : 'Device' }}</option>
                    </select>
                </div>

                <!-- Notes -->
                <div class="sm:col-span-2">
                    <label for="notes" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}
                    </label>
                    <textarea name="notes" id="notes" rows="3" class="erp-input w-full">{{ old('notes', $attendanceRecord->notes) }}</textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-3">
                <a href="{{ route('attendance-records.index') }}" class="btn-secondary px-6 py-2">
                    {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                </a>
                <button type="submit" class="btn-primary px-6 py-2">
                    {{ app()->getLocale() === 'ar' ? 'تحديث السجل' : 'Update Record' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
