@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'إعدادات الرواتب' : 'Payroll Settings')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
            {{ app()->getLocale() === 'ar' ? 'إعدادات الرواتب' : 'Payroll Settings' }}
        </h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            {{ app()->getLocale() === 'ar' ? 'تكوين دورة الرواتب والسياسات.' : 'Configure payroll cycle and policies.' }}
        </p>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 text-sm font-semibold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
        <form action="{{ route('payroll-settings.update', $setting->id) }}" method="POST" class="p-6 sm:p-8 space-y-8">
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

            <!-- Payroll Cycle Section -->
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4 pb-2 border-b border-slate-200 dark:border-slate-700">{{ app()->getLocale() === 'ar' ? 'دورة الرواتب' : 'Payroll Cycle' }}</h2>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="payroll_cycle_type" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'نوع الدورة' : 'Cycle Type' }}</label>
                        <select name="payroll_cycle_type" id="payroll_cycle_type" class="erp-input w-full">
                            <option value="monthly" {{ old('payroll_cycle_type', $setting->payroll_cycle_type?->value) === 'monthly' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'شهري' : 'Monthly' }}</option>
                            <option value="semi_monthly" {{ old('payroll_cycle_type', $setting->payroll_cycle_type?->value) === 'semi_monthly' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'نصف شهري' : 'Semi-Monthly' }}</option>
                            <option value="weekly" {{ old('payroll_cycle_type', $setting->payroll_cycle_type?->value) === 'weekly' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'أسبوعي' : 'Weekly' }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="default_pay_day" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'يوم الدفع الافتراضي' : 'Default Pay Day' }}</label>
                        <input type="number" name="default_pay_day" id="default_pay_day" min="1" max="31" value="{{ old('default_pay_day', $setting->default_pay_day) }}" class="erp-input w-full" dir="ltr">
                    </div>
                    <div>
                        <label for="default_currency" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'العملة الافتراضية' : 'Default Currency' }}</label>
                        <input type="text" name="default_currency" id="default_currency" value="{{ old('default_currency', $setting->default_currency) }}" class="erp-input w-full" dir="ltr" placeholder="e.g. SAR">
                    </div>
                    <div>
                        <label for="payslip_language" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'لغة كشف الراتب' : 'Payslip Language' }}</label>
                        <select name="payslip_language" id="payslip_language" class="erp-input w-full">
                            <option value="ar" {{ old('payslip_language', $setting->payslip_language) === 'ar' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'عربي' : 'Arabic' }}</option>
                            <option value="en" {{ old('payslip_language', $setting->payslip_language) === 'en' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'إنجليزي' : 'English' }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Policies Section -->
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4 pb-2 border-b border-slate-200 dark:border-slate-700">{{ app()->getLocale() === 'ar' ? 'سياسات الرواتب' : 'Payroll Policies' }}</h2>
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <input type="hidden" name="overtime_calculation_enabled" value="0">
                        <input type="checkbox" name="overtime_calculation_enabled" id="overtime_calculation_enabled" value="1" {{ old('overtime_calculation_enabled', $setting->overtime_calculation_enabled) ? 'checked' : '' }} class="w-5 h-5 text-brand-600 rounded border-slate-300 focus:ring-brand-600">
                        <label for="overtime_calculation_enabled" class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ app()->getLocale() === 'ar' ? 'تفعيل حساب العمل الإضافي' : 'Enable Overtime Calculation' }}</label>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="hidden" name="absence_deduction_enabled" value="0">
                        <input type="checkbox" name="absence_deduction_enabled" id="absence_deduction_enabled" value="1" {{ old('absence_deduction_enabled', $setting->absence_deduction_enabled) ? 'checked' : '' }} class="w-5 h-5 text-brand-600 rounded border-slate-300 focus:ring-brand-600">
                        <label for="absence_deduction_enabled" class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ app()->getLocale() === 'ar' ? 'تفعيل خصم الغياب' : 'Enable Absence Deduction' }}</label>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="hidden" name="late_deduction_enabled" value="0">
                        <input type="checkbox" name="late_deduction_enabled" id="late_deduction_enabled" value="1" {{ old('late_deduction_enabled', $setting->late_deduction_enabled) ? 'checked' : '' }} class="w-5 h-5 text-brand-600 rounded border-slate-300 focus:ring-brand-600">
                        <label for="late_deduction_enabled" class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ app()->getLocale() === 'ar' ? 'تفعيل خصم التأخير' : 'Enable Late Deduction' }}</label>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="hidden" name="payroll_approval_required" value="0">
                        <input type="checkbox" name="payroll_approval_required" id="payroll_approval_required" value="1" {{ old('payroll_approval_required', $setting->payroll_approval_required) ? 'checked' : '' }} class="w-5 h-5 text-brand-600 rounded border-slate-300 focus:ring-brand-600">
                        <label for="payroll_approval_required" class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ app()->getLocale() === 'ar' ? 'الموافقة مطلوبة قبل صرف الراتب' : 'Payroll Approval Required' }}</label>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                <button type="submit" class="btn-primary px-6 py-2">{{ app()->getLocale() === 'ar' ? 'حفظ الإعدادات' : 'Save Settings' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
