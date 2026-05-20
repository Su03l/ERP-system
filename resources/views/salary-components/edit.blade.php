@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'تعديل مكون راتب' : 'Edit Salary Component')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">
                {{ app()->getLocale() === 'ar' ? 'تعديل مكون الراتب' : 'Edit Salary Component' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 font-medium">
                {{ app()->getLocale() === 'ar' ? 'تحديث بيانات البدلات والاستقطاعات.' : 'Update allowance and deduction configurations.' }}
            </p>
        </div>
        <a href="{{ route('salary-components.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-all">
            {{ app()->getLocale() === 'ar' ? 'عودة للقائمة' : 'Back to List' }}
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 shadow-xl border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden">
        <form action="{{ route('salary-components.update', $salaryComponent->id) }}" method="POST" class="p-8 sm:p-12 space-y-10">
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

            <!-- Section 1: Identification -->
            <div class="space-y-6">
                <h2 class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-3">
                    <span class="w-8 h-px bg-slate-200 dark:bg-slate-800"></span>
                    {{ app()->getLocale() === 'ar' ? 'بيانات التعريف' : 'Identification' }}
                </h2>
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="name_ar" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="name_ar" id="name_ar" value="{{ old('name_ar', $salaryComponent->name_ar) }}" required class="erp-input w-full p-4 rounded-2xl">
                    </div>
                    <div class="space-y-2">
                        <label for="name_en" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="name_en" id="name_en" value="{{ old('name_en', $salaryComponent->name_en) }}" required class="erp-input w-full p-4 rounded-2xl" dir="ltr">
                    </div>
                    <div class="space-y-2">
                        <label for="code" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'كود المكون' : 'Component Code' }} <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" id="code" value="{{ old('code', $salaryComponent->code) }}" required class="erp-input w-full p-4 rounded-2xl font-mono uppercase" dir="ltr">
                    </div>
                </div>
            </div>

            <!-- Section 2: Type & Calculation -->
            <div class="space-y-6">
                <h2 class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-3">
                    <span class="w-8 h-px bg-slate-200 dark:bg-slate-800"></span>
                    {{ app()->getLocale() === 'ar' ? 'النوع وطريقة الحساب' : 'Type & Calculation' }}
                </h2>
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="type" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'نوع المكون' : 'Component Type' }}</label>
                        <select name="type" id="type" required class="erp-input w-full p-4 rounded-2xl bg-white dark:bg-slate-900">
                            <option value="allowance" {{ old('type', $salaryComponent->type->value) === 'allowance' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'بدل (إضافة)' : 'Allowance' }}</option>
                            <option value="deduction" {{ old('type', $salaryComponent->type->value) === 'deduction' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'استقطاع (خصم)' : 'Deduction' }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label for="calculation_type" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'طريقة الحساب' : 'Calculation Mode' }}</label>
                        <select name="calculation_type" id="calculation_type" required class="erp-input w-full p-4 rounded-2xl bg-white dark:bg-slate-900">
                            <option value="fixed" {{ old('calculation_type', $salaryComponent->calculation_type->value) === 'fixed' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'مبلغ ثابت' : 'Fixed Amount' }}</option>
                            <option value="percentage" {{ old('calculation_type', $salaryComponent->calculation_type->value) === 'percentage' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'نسبة مئوية' : 'Percentage' }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label for="default_amount" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'المبلغ الافتراضي' : 'Default Amount' }}</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="default_amount" id="default_amount" value="{{ old('default_amount', $salaryComponent->default_amount) }}" class="erp-input w-full p-4 rounded-2xl" dir="ltr">
                            <span class="absolute inset-y-0 right-4 flex items-center text-xs font-bold text-slate-400 pointer-events-none">SAR</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label for="default_percentage" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'النسبة الافتراضية' : 'Default Percentage' }}</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="default_percentage" id="default_percentage" value="{{ old('default_percentage', $salaryComponent->default_percentage) }}" class="erp-input w-full p-4 rounded-2xl" dir="ltr">
                            <span class="absolute inset-y-0 right-4 flex items-center text-xs font-bold text-slate-400 pointer-events-none">%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Flags & Status -->
            <div class="space-y-6">
                <h2 class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-3">
                    <span class="w-8 h-px bg-slate-200 dark:bg-slate-800"></span>
                    {{ app()->getLocale() === 'ar' ? 'الخصائص والحالة' : 'Attributes & Status' }}
                </h2>
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <input type="checkbox" name="is_taxable" id="is_taxable" value="1" {{ old('is_taxable', $salaryComponent->is_taxable) ? 'checked' : '' }} class="mt-1 w-5 h-5 text-brand-600 rounded-lg border-slate-300">
                        <div>
                            <label for="is_taxable" class="block text-sm font-black text-slate-700 dark:text-slate-300 leading-none mb-1">{{ app()->getLocale() === 'ar' ? 'خاضع للضريبة' : 'Taxable Component' }}</label>
                            <p class="text-[10px] text-slate-500 font-medium">{{ app()->getLocale() === 'ar' ? 'يتم تضمين هذا المكون في وعاء الضريبة.' : 'This component will be included in tax calculations.' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <input type="checkbox" name="is_recurring" id="is_recurring" value="1" {{ old('is_recurring', $salaryComponent->is_recurring) ? 'checked' : '' }} class="mt-1 w-5 h-5 text-brand-600 rounded-lg border-slate-300">
                        <div>
                            <label for="is_recurring" class="block text-sm font-black text-slate-700 dark:text-slate-300 leading-none mb-1">{{ app()->getLocale() === 'ar' ? 'مكون متكرر' : 'Recurring Component' }}</label>
                            <p class="text-[10px] text-slate-500 font-medium">{{ app()->getLocale() === 'ar' ? 'يضاف تلقائياً لكل دورة راتب.' : 'Automatically added to every payroll cycle.' }}</p>
                        </div>
                    </div>
                    <div class="space-y-2 sm:col-span-2">
                        <label for="status" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'حالة المكون' : 'Availability Status' }}</label>
                        <select name="status" id="status" class="erp-input w-full p-4 rounded-2xl bg-white dark:bg-slate-900 sm:w-1/2">
                            <option value="active" {{ old('status', $salaryComponent->status->value) === 'active' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'نشط (متاح للاستخدام)' : 'Active' }}</option>
                            <option value="inactive" {{ old('status', $salaryComponent->status->value) === 'inactive' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive' }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="pt-10 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row justify-end gap-4">
                <a href="{{ route('salary-components.index') }}" class="inline-flex items-center justify-center px-8 py-4 text-sm font-bold text-slate-500 hover:text-slate-700 transition-colors">
                    {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                </a>
                <button type="submit" class="inline-flex items-center justify-center px-10 py-4 text-sm font-black text-white bg-brand-600 hover:bg-brand-700 rounded-2xl shadow-xl shadow-brand-500/20 transition-all active:scale-95">
                    {{ app()->getLocale() === 'ar' ? 'تحديث المكون' : 'Update Component' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
