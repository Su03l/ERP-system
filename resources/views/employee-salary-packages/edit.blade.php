@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'تعديل حزمة راتب' : 'Edit Salary Package')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">
                {{ app()->getLocale() === 'ar' ? 'تعديل حزمة الراتب' : 'Edit Salary Package' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 font-medium">
                {{ app()->getLocale() === 'ar' ? 'تعديل تخصيصات الراتب والبدلات للموظف.' : 'Modify salary allocations and allowances for the employee.' }}
            </p>
        </div>
        <a href="{{ route('employee-salary-packages.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-all">
            {{ app()->getLocale() === 'ar' ? 'عودة للقائمة' : 'Back to List' }}
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 shadow-xl border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden">
        <form action="{{ route('employee-salary-packages.update', $employeeSalaryPackage->id) }}" method="POST" class="p-8 sm:p-12 space-y-10">
            @csrf
            @method('PUT')

            @if($errors->any())
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-100 text-rose-700 text-xs font-bold flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                    <div>
                        <p class="mb-1 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'يرجى تصحيح الأخطاء التالية:' : 'Please correct the following errors:' }}</p>
                        <ul class="list-disc list-inside opacity-80 font-medium">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Section 1: Employee Selection -->
            <div class="space-y-6">
                <h2 class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-3">
                    <span class="w-8 h-px bg-slate-200 dark:bg-slate-800"></span>
                    {{ app()->getLocale() === 'ar' ? 'اختيار الموظف' : 'Target Employee' }}
                </h2>
                <div class="space-y-2">
                    <label for="employee_id" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'الموظف المستهدف' : 'Employee' }} <span class="text-rose-500">*</span></label>
                    <select name="employee_id" id="employee_id" required class="erp-input w-full p-4 rounded-2xl bg-white dark:bg-slate-900 border-slate-200 focus:ring-brand-500">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id', $employeeSalaryPackage->employee_id) == $emp->id ? 'selected' : '' }}>
                                {{ $emp->first_name }} {{ $emp->last_name }} (#{{ $emp->employee_number }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Section 2: Core Salary -->
            <div class="space-y-6">
                <h2 class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-3">
                    <span class="w-8 h-px bg-slate-200 dark:bg-slate-800"></span>
                    {{ app()->getLocale() === 'ar' ? 'الراتب والبدلات الرئيسية' : 'Core Compensation' }}
                </h2>
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="basic_salary" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'الراتب الأساسي' : 'Basic Salary' }} <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="number" step="0.01" name="basic_salary" id="basic_salary" value="{{ old('basic_salary', $employeeSalaryPackage->basic_salary) }}" required class="erp-input w-full p-4 rounded-2xl" dir="ltr">
                            <span class="absolute inset-y-0 right-4 flex items-center text-xs font-bold text-slate-400 pointer-events-none">SAR</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label for="housing_allowance" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'بدل السكن' : 'Housing Allowance' }}</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="housing_allowance" id="housing_allowance" value="{{ old('housing_allowance', $employeeSalaryPackage->housing_allowance) }}" class="erp-input w-full p-4 rounded-2xl" dir="ltr">
                            <span class="absolute inset-y-0 right-4 flex items-center text-xs font-bold text-slate-400 pointer-events-none">SAR</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label for="transportation_allowance" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'بدل النقل' : 'Transportation' }}</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="transportation_allowance" id="transportation_allowance" value="{{ old('transportation_allowance', $employeeSalaryPackage->transportation_allowance) }}" class="erp-input w-full p-4 rounded-2xl" dir="ltr">
                            <span class="absolute inset-y-0 right-4 flex items-center text-xs font-bold text-slate-400 pointer-events-none">SAR</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Period & Validity -->
            <div class="space-y-6">
                <h2 class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-3">
                    <span class="w-8 h-px bg-slate-200 dark:bg-slate-800"></span>
                    {{ app()->getLocale() === 'ar' ? 'مدة السريان والحالة' : 'Validity & Status' }}
                </h2>
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="effective_from" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'تاريخ البدء' : 'Effective From' }} <span class="text-rose-500">*</span></label>
                        <input type="date" name="effective_from" id="effective_from" value="{{ old('effective_from', $employeeSalaryPackage->effective_from?->format('Y-m-d')) }}" required class="erp-input w-full p-4 rounded-2xl">
                    </div>
                    <div class="space-y-2">
                        <label for="effective_to" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'تاريخ الانتهاء' : 'Effective Until' }}</label>
                        <input type="date" name="effective_to" id="effective_to" value="{{ old('effective_to', $employeeSalaryPackage->effective_to?->format('Y-m-d')) }}" class="erp-input w-full p-4 rounded-2xl">
                    </div>
                    <div class="space-y-2">
                        <label for="status" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'حالة الباقة' : 'Package Status' }}</label>
                        <select name="status" id="status" class="erp-input w-full p-4 rounded-2xl bg-white dark:bg-slate-900">
                            <option value="active" {{ old('status', $employeeSalaryPackage->status->value) === 'active' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'نشطة (مفعلة)' : 'Active' }}</option>
                            <option value="ended" {{ old('status', $employeeSalaryPackage->status->value) === 'ended' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'منتهية' : 'Ended' }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="pt-10 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row justify-end gap-4">
                <a href="{{ route('employee-salary-packages.index') }}" class="inline-flex items-center justify-center px-8 py-4 text-sm font-bold text-slate-500 hover:text-slate-700 transition-colors">
                    {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                </a>
                <button type="submit" class="inline-flex items-center justify-center px-10 py-4 text-sm font-black text-white bg-brand-600 hover:bg-brand-700 rounded-2xl shadow-xl shadow-brand-500/20 transition-all active:scale-95">
                    {{ app()->getLocale() === 'ar' ? 'تحديث الباقة' : 'Update Package' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
