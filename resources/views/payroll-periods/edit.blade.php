@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تعديل فترة رواتب' : 'Edit Payroll Period')
@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'تعديل فترة الرواتب' : 'Edit Payroll Period' }}</h1><a href="{{ route('payroll-periods.index') }}" class="btn-secondary px-4 py-2 text-sm">{{ app()->getLocale() === 'ar' ? 'عودة' : 'Back' }}</a></div>
    <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl">
        <form action="{{ route('payroll-periods.update', $payrollPeriod->id) }}" method="POST" class="p-6 sm:p-8 space-y-6">@csrf @method('PUT')
            @if($errors->any())<div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 text-sm"><ul class="list-disc px-4 space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div><label for="name_ar" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (AR)' }}</label><input type="text" name="name_ar" id="name_ar" value="{{ old('name_ar', $payrollPeriod->name_ar) }}" required class="erp-input w-full"></div>
                <div><label for="name_en" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (EN)' }}</label><input type="text" name="name_en" id="name_en" value="{{ old('name_en', $payrollPeriod->name_en) }}" required class="erp-input w-full" dir="ltr"></div>
                <div><label for="starts_on" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'البداية' : 'Start' }}</label><input type="date" name="starts_on" id="starts_on" value="{{ old('starts_on', $payrollPeriod->starts_on->format('Y-m-d')) }}" required class="erp-input w-full"></div>
                <div><label for="ends_on" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'النهاية' : 'End' }}</label><input type="date" name="ends_on" id="ends_on" value="{{ old('ends_on', $payrollPeriod->ends_on->format('Y-m-d')) }}" required class="erp-input w-full"></div>
                <div><label for="pay_date" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'تاريخ الدفع' : 'Pay Date' }}</label><input type="date" name="pay_date" id="pay_date" value="{{ old('pay_date', $payrollPeriod->pay_date->format('Y-m-d')) }}" required class="erp-input w-full"></div>
                <div><label for="status" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</label>
                    <select name="status" id="status" class="erp-input w-full"><option value="open" {{ old('status', $payrollPeriod->status->value) === 'open' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'مفتوح' : 'Open' }}</option><option value="closed" {{ old('status', $payrollPeriod->status->value) === 'closed' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'مغلق' : 'Closed' }}</option></select></div>
            </div>
            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-3"><a href="{{ route('payroll-periods.index') }}" class="btn-secondary px-6 py-2">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a><button type="submit" class="btn-primary px-6 py-2">{{ app()->getLocale() === 'ar' ? 'تحديث' : 'Update' }}</button></div>
        </form>
    </div>
</div>
@endsection
