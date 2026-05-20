@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'إنشاء فترة رواتب' : 'Create Payroll Period')
@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'إنشاء فترة رواتب جديدة' : 'Create Payroll Period' }}</h1><a href="{{ route('payroll-periods.index') }}" class="btn-secondary px-4 py-2 text-sm">{{ app()->getLocale() === 'ar' ? 'عودة' : 'Back' }}</a></div>
    <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl">
        <form action="{{ route('payroll-periods.store') }}" method="POST" class="p-6 sm:p-8 space-y-6">@csrf
            @if($errors->any())<div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 text-sm"><ul class="list-disc px-4 space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div><label for="name_ar" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }} <span class="text-rose-500">*</span></label><input type="text" name="name_ar" id="name_ar" value="{{ old('name_ar') }}" required class="erp-input w-full"></div>
                <div><label for="name_en" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }} <span class="text-rose-500">*</span></label><input type="text" name="name_en" id="name_en" value="{{ old('name_en') }}" required class="erp-input w-full" dir="ltr"></div>
                <div><label for="starts_on" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'تاريخ البداية' : 'Start Date' }} <span class="text-rose-500">*</span></label><input type="date" name="starts_on" id="starts_on" value="{{ old('starts_on') }}" required class="erp-input w-full"></div>
                <div><label for="ends_on" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'تاريخ النهاية' : 'End Date' }} <span class="text-rose-500">*</span></label><input type="date" name="ends_on" id="ends_on" value="{{ old('ends_on') }}" required class="erp-input w-full"></div>
                <div><label for="pay_date" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'تاريخ الدفع' : 'Pay Date' }} <span class="text-rose-500">*</span></label><input type="date" name="pay_date" id="pay_date" value="{{ old('pay_date') }}" required class="erp-input w-full"></div>
                <div><label for="status" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</label>
                    <select name="status" id="status" class="erp-input w-full"><option value="open">{{ app()->getLocale() === 'ar' ? 'مفتوح' : 'Open' }}</option><option value="closed">{{ app()->getLocale() === 'ar' ? 'مغلق' : 'Closed' }}</option></select></div>
            </div>
            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-3"><a href="{{ route('payroll-periods.index') }}" class="btn-secondary px-6 py-2">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a><button type="submit" class="btn-primary px-6 py-2">{{ app()->getLocale() === 'ar' ? 'حفظ' : 'Save' }}</button></div>
        </form>
    </div>
</div>
@endsection
