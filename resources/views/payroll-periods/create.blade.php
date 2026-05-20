@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'إنشاء فترة رواتب' : 'Create Payroll Period')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">
                {{ app()->getLocale() === 'ar' ? 'إنشاء فترة رواتب جديدة' : 'Create Payroll Period' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 font-medium">
                {{ app()->getLocale() === 'ar' ? 'تحديد المدى الزمني وتاريخ صرف الرواتب للموظفين.' : 'Define the date range and payment schedule for employee payroll.' }}
            </p>
        </div>
        <a href="{{ route('payroll-periods.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-all">
            {{ app()->getLocale() === 'ar' ? 'عودة للقائمة' : 'Back to List' }}
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 shadow-xl border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden">
        <form action="{{ route('payroll-periods.store') }}" method="POST" class="p-8 sm:p-12 space-y-10">
            @csrf
            
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

            <!-- Section 1: Basic Information -->
            <div class="space-y-6">
                <h2 class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-3">
                    <span class="w-8 h-px bg-slate-200 dark:bg-slate-800"></span>
                    {{ app()->getLocale() === 'ar' ? 'المعلومات الأساسية' : 'General Info' }}
                </h2>
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="name_ar" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="name_ar" id="name_ar" value="{{ old('name_ar') }}" required placeholder="مثال: يناير 2024" class="erp-input w-full p-4 rounded-2xl">
                    </div>
                    <div class="space-y-2">
                        <label for="name_en" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="name_en" id="name_en" value="{{ old('name_en') }}" required placeholder="e.g. January 2024" class="erp-input w-full p-4 rounded-2xl" dir="ltr">
                    </div>
                </div>
            </div>

            <!-- Section 2: Timing & Schedule -->
            <div class="space-y-6">
                <h2 class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-3">
                    <span class="w-8 h-px bg-slate-200 dark:bg-slate-800"></span>
                    {{ app()->getLocale() === 'ar' ? 'الجدول الزمني' : 'Timing & Schedule' }}
                </h2>
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-3">
                    <div class="space-y-2">
                        <label for="starts_on" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'تاريخ البداية' : 'Start Date' }} <span class="text-rose-500">*</span></label>
                        <input type="date" name="starts_on" id="starts_on" value="{{ old('starts_on') }}" required class="erp-input w-full p-4 rounded-2xl">
                    </div>
                    <div class="space-y-2">
                        <label for="ends_on" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'تاريخ النهاية' : 'End Date' }} <span class="text-rose-500">*</span></label>
                        <input type="date" name="ends_on" id="ends_on" value="{{ old('ends_on') }}" required class="erp-input w-full p-4 rounded-2xl">
                    </div>
                    <div class="space-y-2">
                        <label for="pay_date" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'تاريخ الدفع' : 'Pay Date' }} <span class="text-rose-500">*</span></label>
                        <input type="date" name="pay_date" id="pay_date" value="{{ old('pay_date') }}" required class="erp-input w-full p-4 rounded-2xl">
                    </div>
                </div>
            </div>

            <!-- Section 3: Configuration -->
            <div class="space-y-6">
                <h2 class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-3">
                    <span class="w-8 h-px bg-slate-200 dark:bg-slate-800"></span>
                    {{ app()->getLocale() === 'ar' ? 'الإعدادات' : 'Configuration' }}
                </h2>
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="status" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'الحالة الأولية' : 'Initial Status' }}</label>
                        <select name="status" id="status" class="erp-input w-full p-4 rounded-2xl bg-white dark:bg-slate-900">
                            <option value="open">{{ app()->getLocale() === 'ar' ? 'مفتوح (جاهز للتشغيل)' : 'Open (Ready for Run)' }}</option>
                            <option value="closed">{{ app()->getLocale() === 'ar' ? 'مغلق' : 'Closed' }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="pt-10 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row justify-end gap-4">
                <a href="{{ route('payroll-periods.index') }}" class="inline-flex items-center justify-center px-8 py-4 text-sm font-bold text-slate-500 hover:text-slate-700 transition-colors">
                    {{ app()->getLocale() === 'ar' ? 'إلغاء العملية' : 'Cancel' }}
                </a>
                <button type="submit" class="inline-flex items-center justify-center px-10 py-4 text-sm font-black text-white bg-brand-600 hover:bg-brand-700 rounded-2xl shadow-xl shadow-brand-500/20 transition-all active:scale-95">
                    {{ app()->getLocale() === 'ar' ? 'حفظ الفترة' : 'Save Period' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
