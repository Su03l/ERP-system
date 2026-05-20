@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تعديل مكون راتب' : 'Edit Salary Component')
@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <div><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'تعديل مكون الراتب' : 'Edit Salary Component' }}</h1></div>
        <a href="{{ route('salary-components.index') }}" class="btn-secondary px-4 py-2 text-sm">{{ app()->getLocale() === 'ar' ? 'عودة' : 'Back' }}</a>
    </div>
    <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
        <form action="{{ route('salary-components.update', $salaryComponent->id) }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf @method('PUT')
            @if($errors->any())<div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 text-sm"><ul class="list-disc px-4 space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div><label for="name_ar" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }} <span class="text-rose-500">*</span></label><input type="text" name="name_ar" id="name_ar" value="{{ old('name_ar', $salaryComponent->name_ar) }}" required class="erp-input w-full"></div>
                <div><label for="name_en" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }} <span class="text-rose-500">*</span></label><input type="text" name="name_en" id="name_en" value="{{ old('name_en', $salaryComponent->name_en) }}" required class="erp-input w-full" dir="ltr"></div>
                <div><label for="code" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'الكود' : 'Code' }} <span class="text-rose-500">*</span></label><input type="text" name="code" id="code" value="{{ old('code', $salaryComponent->code) }}" required class="erp-input w-full" dir="ltr"></div>
                <div><label for="type" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'النوع' : 'Type' }}</label>
                    <select name="type" id="type" class="erp-input w-full"><option value="allowance" {{ old('type', $salaryComponent->type->value) === 'allowance' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'بدل' : 'Allowance' }}</option><option value="deduction" {{ old('type', $salaryComponent->type->value) === 'deduction' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'استقطاع' : 'Deduction' }}</option></select></div>
                <div><label for="calculation_type" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'طريقة الحساب' : 'Calculation Type' }}</label>
                    <select name="calculation_type" id="calculation_type" class="erp-input w-full"><option value="fixed" {{ old('calculation_type', $salaryComponent->calculation_type->value) === 'fixed' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'مبلغ ثابت' : 'Fixed' }}</option><option value="percentage" {{ old('calculation_type', $salaryComponent->calculation_type->value) === 'percentage' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'نسبة' : 'Percentage' }}</option></select></div>
                <div><label for="default_amount" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'المبلغ الافتراضي' : 'Default Amount' }}</label><input type="number" step="0.01" name="default_amount" id="default_amount" value="{{ old('default_amount', $salaryComponent->default_amount) }}" class="erp-input w-full" dir="ltr"></div>
                <div><label for="default_percentage" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'النسبة الافتراضية' : 'Default %' }}</label><input type="number" step="0.01" name="default_percentage" id="default_percentage" value="{{ old('default_percentage', $salaryComponent->default_percentage) }}" class="erp-input w-full" dir="ltr"></div>
                <div class="flex items-center gap-3"><input type="hidden" name="is_taxable" value="0"><input type="checkbox" name="is_taxable" id="is_taxable" value="1" {{ old('is_taxable', $salaryComponent->is_taxable) ? 'checked' : '' }} class="w-5 h-5 text-brand-600 rounded border-slate-300"><label for="is_taxable" class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ app()->getLocale() === 'ar' ? 'خاضع للضريبة' : 'Taxable' }}</label></div>
                <div class="flex items-center gap-3"><input type="hidden" name="is_recurring" value="0"><input type="checkbox" name="is_recurring" id="is_recurring" value="1" {{ old('is_recurring', $salaryComponent->is_recurring) ? 'checked' : '' }} class="w-5 h-5 text-brand-600 rounded border-slate-300"><label for="is_recurring" class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ app()->getLocale() === 'ar' ? 'متكرر' : 'Recurring' }}</label></div>
                <div><label for="status" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</label>
                    <select name="status" id="status" class="erp-input w-full"><option value="active" {{ old('status', $salaryComponent->status->value) === 'active' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}</option><option value="inactive" {{ old('status', $salaryComponent->status->value) === 'inactive' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive' }}</option></select></div>
            </div>
            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-3">
                <a href="{{ route('salary-components.index') }}" class="btn-secondary px-6 py-2">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a>
                <button type="submit" class="btn-primary px-6 py-2">{{ app()->getLocale() === 'ar' ? 'تحديث' : 'Update' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
