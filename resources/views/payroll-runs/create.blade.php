@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تشغيل الرواتب' : 'Generate Payroll Run')
@section('content')
<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'تشغيل الرواتب' : 'Generate Payroll Run' }}</h1>
        <a href="{{ route('payroll-runs.index') }}" class="btn-secondary px-4 py-2 text-sm">{{ app()->getLocale() === 'ar' ? 'عودة' : 'Back' }}</a>
    </div>
    <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl">
        <form action="{{ route('payroll-runs.store') }}" method="POST" class="p-6 sm:p-8 space-y-6" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من تشغيل الرواتب لهذه الفترة؟ هذه العملية قد تستغرق وقتاً.' : 'Are you sure you want to generate payroll for this period? This may take a moment.' }}');">
            @csrf
            @if($errors->any())<div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 text-sm"><ul class="list-disc px-4 space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-sm flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                <div>{{ app()->getLocale() === 'ar' ? 'سيتم حساب رواتب جميع الموظفين المؤهلين. تأكد من إغلاق سجلات الحضور قبل التشغيل.' : 'Payroll will be calculated for all eligible employees. Ensure attendance records are finalized before generating.' }}</div>
            </div>

            <div>
                <label for="payroll_period_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'فترة الرواتب' : 'Payroll Period' }} <span class="text-rose-500">*</span></label>
                <select name="payroll_period_id" id="payroll_period_id" required class="erp-input w-full">
                    <option value="">{{ app()->getLocale() === 'ar' ? '-- اختر الفترة --' : '-- Select Period --' }}</option>
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}" {{ old('payroll_period_id') == $period->id ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' ? $period->name_ar : $period->name_en }} ({{ $period->starts_on->format('Y-m-d') }} → {{ $period->ends_on->format('Y-m-d') }})
                        </option>
                    @endforeach
                </select>
                @if($periods->isEmpty())
                    <p class="mt-2 text-sm text-rose-500 font-semibold">{{ app()->getLocale() === 'ar' ? 'لا توجد فترات مفتوحة. أنشئ فترة أولاً.' : 'No open periods. Create a period first.' }}</p>
                @endif
            </div>

            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-3">
                <a href="{{ route('payroll-runs.index') }}" class="btn-secondary px-6 py-2">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a>
                <button type="submit" class="btn-primary px-6 py-2" {{ $periods->isEmpty() ? 'disabled' : '' }}>{{ app()->getLocale() === 'ar' ? 'تشغيل الرواتب' : 'Generate Payroll' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
