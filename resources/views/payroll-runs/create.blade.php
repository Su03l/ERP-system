@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'تشغيل الرواتب' : 'Generate Payroll Run')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">
                {{ app()->getLocale() === 'ar' ? 'تشغيل الرواتب' : 'Generate Payroll' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 font-medium">
                {{ app()->getLocale() === 'ar' ? 'بدء عملية احتساب مستحقات الموظفين لفترة محددة.' : 'Initiate the calculation process for employee entitlements for a specific period.' }}
            </p>
        </div>
        <a href="{{ route('payroll-runs.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-all">
            {{ app()->getLocale() === 'ar' ? 'عودة للقائمة' : 'Back to List' }}
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 shadow-xl border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden">
        <form action="{{ route('payroll-runs.store') }}" method="POST" class="p-8 sm:p-12 space-y-8" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من تشغيل الرواتب لهذه الفترة؟ هذه العملية قد تستغرق وقتاً.' : 'Are you sure you want to generate payroll for this period? This may take a moment.' }}');">
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

            <div class="p-6 rounded-2xl bg-amber-50 border border-amber-100 dark:bg-amber-900/10 dark:border-amber-900/30 text-amber-800 dark:text-amber-400 text-sm flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                </div>
                <div>
                    <h3 class="font-black uppercase tracking-wider mb-1">{{ app()->getLocale() === 'ar' ? 'تنبيه قبل التشغيل' : 'Critical Notice' }}</h3>
                    <p class="opacity-80 leading-relaxed">{{ app()->getLocale() === 'ar' ? 'سيتم حساب رواتب جميع الموظفين بناءً على باقاتهم النشطة. يرجى التأكد من إغلاق سجلات الحضور والموافقة على طلبات الإجازات قبل المتابعة.' : 'Payroll will be calculated based on active salary packages. Ensure attendance records are locked and leave requests are approved before proceeding.' }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <label for="payroll_period_id" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'فترة الرواتب المستهدفة' : 'Target Payroll Period' }} <span class="text-rose-500">*</span></label>
                <div class="relative group">
                    <select name="payroll_period_id" id="payroll_period_id" required class="erp-input w-full p-4 rounded-2xl bg-white dark:bg-slate-900 border-slate-200 focus:ring-brand-500 appearance-none">
                        <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر الفترة المفتوحة --' : '-- Select an Open Period --' }}</option>
                        @foreach($periods as $period)
                            <option value="{{ $period->id }}" {{ old('payroll_period_id') == $period->id ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? $period->name_ar : $period->name_en }} 
                                ({{ $period->starts_on->format('Y-m-d') }} → {{ $period->ends_on->format('Y-m-d') }})
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
                @if($periods->isEmpty())
                    <div class="flex items-center gap-2 p-3 rounded-xl bg-rose-50 text-rose-600 text-xs font-bold border border-rose-100">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                        {{ app()->getLocale() === 'ar' ? 'لا توجد فترات مفتوحة حالياً. يرجى إنشاء فترة أولاً.' : 'No open periods found. Please create a period first.' }}
                    </div>
                @endif
            </div>

            <div class="pt-8 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row justify-end gap-4">
                <a href="{{ route('payroll-runs.index') }}" class="inline-flex items-center justify-center px-8 py-4 text-sm font-bold text-slate-500 hover:text-slate-700 transition-colors">
                    {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                </a>
                <button type="submit" class="inline-flex items-center justify-center px-10 py-4 text-sm font-black text-white bg-brand-600 hover:bg-brand-700 rounded-2xl shadow-xl shadow-brand-500/20 transition-all active:scale-95 group" {{ $periods->isEmpty() ? 'disabled' : '' }}>
                    <svg class="w-5 h-5 mr-2 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    {{ app()->getLocale() === 'ar' ? 'بدء التشغيل والاحتساب' : 'Generate & Calculate' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
