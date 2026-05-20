@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'طلب إجازة جديد' : 'New Leave Request')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                {{ app()->getLocale() === 'ar' ? 'طلب إجازة جديد' : 'New Leave Request' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ app()->getLocale() === 'ar' ? 'قم بتعبئة النموذج أدناه لتقديم طلب الإجازة.' : 'Fill the form below to submit a leave request.' }}
            </p>
        </div>
        <a href="{{ route('leave-requests.index') }}" class="btn-secondary px-4 py-2 text-sm">
            {{ app()->getLocale() === 'ar' ? 'عودة للطلبات' : 'Back to Requests' }}
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
        <form action="{{ route('leave-requests.store') }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf

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
                    <select name="employee_id" id="employee_id" required class="erp-input w-full">
                        <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر الموظف --' : '-- Select Employee --' }}</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_number }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Leave Type -->
                <div class="sm:col-span-2">
                    <label for="leave_type_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ app()->getLocale() === 'ar' ? 'نوع الإجازة' : 'Leave Type' }} <span class="text-rose-500">*</span>
                    </label>
                    <select name="leave_type_id" id="leave_type_id" required class="erp-input w-full">
                        <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر نوع الإجازة --' : '-- Select Leave Type --' }}</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? $type->name_ar : $type->name_en }} ({{ $type->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Start Date -->
                <div>
                    <label for="start_date" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ app()->getLocale() === 'ar' ? 'تاريخ البداية' : 'Start Date' }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" required
                        class="erp-input w-full">
                </div>

                <!-- End Date -->
                <div>
                    <label for="end_date" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ app()->getLocale() === 'ar' ? 'تاريخ النهاية' : 'End Date' }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" required
                        class="erp-input w-full">
                </div>
                
                <!-- Total Days -->
                <div>
                    <label for="total_days" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ app()->getLocale() === 'ar' ? 'عدد الأيام' : 'Total Days' }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="0.5" min="0.5" name="total_days" id="total_days" value="{{ old('total_days') }}" required
                        class="erp-input w-full" dir="ltr">
                </div>

                <!-- Attachment -->
                <div>
                    <label for="attachment" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ app()->getLocale() === 'ar' ? 'المرفقات' : 'Attachment' }}
                    </label>
                    <input type="file" name="attachment" id="attachment" class="w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/30 dark:file:text-brand-400">
                </div>

                <!-- Reason -->
                <div class="sm:col-span-2">
                    <label for="reason" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ app()->getLocale() === 'ar' ? 'السبب' : 'Reason' }} <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="reason" id="reason" rows="4" required class="erp-input w-full">{{ old('reason') }}</textarea>
                </div>
                
                <input type="hidden" name="status" value="draft">
            </div>

            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-3">
                <a href="{{ route('leave-requests.index') }}" class="btn-secondary px-6 py-2">
                    {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                </a>
                <button type="submit" class="btn-primary px-6 py-2">
                    {{ app()->getLocale() === 'ar' ? 'حفظ كمسودة' : 'Save as Draft' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
