@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'إضافة مكون راتب' : 'Create Salary Component')
@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <div><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'إضافة مكون راتب جديد' : 'Create Salary Component' }}</h1></div>
        <a href="{{ route('salary-components.index') }}" class="btn-secondary px-4 py-2 text-sm">{{ app()->getLocale() === 'ar' ? 'عودة' : 'Back' }}</a>
    </div>
    <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
        <form action="{{ route('salary-components.store') }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf
            @if($errors->any())<div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 text-sm"><ul class="list-disc px-4 space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div><label for="name_ar" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }} <span class="text-rose-500">*</span></label><input type="text" name="name_ar" id="name_ar" value="{{ old('name_ar') }}" required class="erp-input w-full"></div>
                <div><label for="name_en" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }} <span class="text-rose-500">*</span></label><input type="text" name="name_en" id="name_en" value="{{ old('name_en') }}" required class="erp-input w-full" dir="ltr"></div>
                <div><label for="code" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'الكود' : 'Code' }} <span class="text-rose-500">*</span></label><input type="text" name="code" id="code" value="{{ old('code') }}" required class="erp-input w-full" dir="ltr"></div>
                <div><label for="type" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'النوع' : 'Type' }} <span class="text-rose-500">*</span></label>
                    <select name="type" id="type" required class="erp-input w-full"><option value="allowance" {{ old('type') === 'allowance' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'بدل' : 'Allowance' }}</option><option value="deduction" {{ old('type') === 'deduction' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'استقطاع' : 'Deduction' }}</option></select></div>
                <div><label for="calculation_type" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'طريقة الحساب' : 'Calculation Type' }} <span class="text-rose-500">*</span></label>
                    <select name="calculation_type" id="calculation_type" required class="erp-input w-full"><option value="fixed" {{ old('calculation_type') === 'fixed' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'مبلغ ثابت' : 'Fixed Amount' }}</option><option value="percentage" {{ old('calculation_type') === 'percentage' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'نسبة مئوية' : 'Percentage' }}</option></select></div>
                <div><label for="default_amount" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'المبلغ الافتراضي' : 'Default Amount' }}</label><input type="number" step="0.01" name="default_amount" id="default_amount" value="{{ old('default_amount', '0.00') }}" class="erp-input w-full" dir="ltr"></div>
                <div><label for="default_percentage" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'النسبة الافتراضية' : 'Default Percentage' }}</label><input type="number" step="0.01" name="default_percentage" id="default_percentage" value="{{ old('default_percentage', '0.00') }}" class="erp-input w-full" dir="ltr"></div>
                <div class="flex items-center gap-3"><input type="hidden" name="is_taxable" value="0"><input type="checkbox" name="is_taxable" id="is_taxable" value="1" {{ old('is_taxable') ? 'checked' : '' }} class="w-5 h-5 text-brand-600 rounded border-slate-300"><label for="is_taxable" class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ app()->getLocale() === 'ar' ? 'خاضع للضريبة' : 'Taxable' }}</label></div>
                <div class="flex items-center gap-3"><input type="hidden" name="is_recurring" value="0"><input type="checkbox" name="is_recurring" id="is_recurring" value="1" {{ old('is_recurring', 1) ? 'checked' : '' }} class="w-5 h-5 text-brand-600 rounded border-slate-300"><label for="is_recurring" class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ app()->getLocale() === 'ar' ? 'متكرر' : 'Recurring' }}</label></div>
                <div><label for="status" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</label>
                    <select name="status" id="status" class="erp-input w-full"><option value="active">{{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}</option><option value="inactive">{{ app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive' }}</option></select></div>
            </div>
            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-3">
                <a href="{{ route('salary-components.index') }}" class="btn-secondary px-6 py-2">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a>
                <button type="submit" class="btn-primary px-6 py-2">{{ app()->getLocale() === 'ar' ? 'حفظ' : 'Save' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
