@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'إضافة نوع إجازة' : 'Create Leave Type')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                {{ app()->getLocale() === 'ar' ? 'إضافة نوع إجازة جديد' : 'Create New Leave Type' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ app()->getLocale() === 'ar' ? 'قم بتعريف سياسة إجازة جديدة.' : 'Define a new leave policy.' }}
            </p>
        </div>
        <a href="{{ route('leave-types.index') }}" class="btn-secondary px-4 py-2 text-sm">
            {{ app()->getLocale() === 'ar' ? 'عودة للأنواع' : 'Back to Types' }}
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
        <form action="{{ route('leave-types.store') }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf

            @if ($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 text-sm">
                    <ul class="list-disc px-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Name AR -->
                <div>
                    <label for="name_ar" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name_ar" id="name_ar" value="{{ old('name_ar') }}" required
                        class="erp-input w-full">
                </div>

                <!-- Name EN -->
                <div>
                    <label for="name_en" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name_en" id="name_en" value="{{ old('name_en') }}" required
                        class="erp-input w-full" dir="ltr">
                </div>

                <!-- Code -->
                <div>
                    <label for="code" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ app()->getLocale() === 'ar' ? 'الكود' : 'Code' }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="code" id="code" value="{{ old('code') }}" required
                        class="erp-input w-full" dir="ltr" placeholder="e.g. ANNUAL, SICK">
                </div>

                <!-- Default Days -->
                <div>
                    <label for="default_days_per_year" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ app()->getLocale() === 'ar' ? 'عدد الأيام في السنة' : 'Default Days Per Year' }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="0.5" min="0" name="default_days_per_year" id="default_days_per_year" value="{{ old('default_days_per_year', 0) }}" required
                        class="erp-input w-full" dir="ltr">
                </div>

                <!-- Is Paid -->
                <div class="flex items-center gap-3">
                    <input type="hidden" name="is_paid" value="0">
                    <input type="checkbox" name="is_paid" id="is_paid" value="1" {{ old('is_paid', 1) ? 'checked' : '' }}
                        class="w-5 h-5 text-brand-600 rounded border-slate-300 focus:ring-brand-600">
                    <label for="is_paid" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                        {{ app()->getLocale() === 'ar' ? 'إجازة مدفوعة الأجر' : 'Paid Leave' }}
                    </label>
                </div>

                <!-- Requires Approval -->
                <div class="flex items-center gap-3">
                    <input type="hidden" name="requires_approval" value="0">
                    <input type="checkbox" name="requires_approval" id="requires_approval" value="1" {{ old('requires_approval', 1) ? 'checked' : '' }}
                        class="w-5 h-5 text-brand-600 rounded border-slate-300 focus:ring-brand-600">
                    <label for="requires_approval" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                        {{ app()->getLocale() === 'ar' ? 'تتطلب موافقة المدير' : 'Requires Manager Approval' }}
                    </label>
                </div>
                
                <!-- Allow Negative Balance -->
                <div class="flex items-center gap-3">
                    <input type="hidden" name="allow_negative_balance" value="0">
                    <input type="checkbox" name="allow_negative_balance" id="allow_negative_balance" value="1" {{ old('allow_negative_balance', 0) ? 'checked' : '' }}
                        class="w-5 h-5 text-brand-600 rounded border-slate-300 focus:ring-brand-600">
                    <label for="allow_negative_balance" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                        {{ app()->getLocale() === 'ar' ? 'السماح برصيد سالب' : 'Allow Negative Balance' }}
                    </label>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}
                    </label>
                    <select name="status" id="status" class="erp-input w-full">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive' }}</option>
                    </select>
                </div>

                <!-- Description -->
                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ app()->getLocale() === 'ar' ? 'الوصف' : 'Description' }}
                    </label>
                    <textarea name="description" id="description" rows="3" class="erp-input w-full">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-3">
                <a href="{{ route('leave-types.index') }}" class="btn-secondary px-6 py-2">
                    {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                </a>
                <button type="submit" class="btn-primary px-6 py-2">
                    {{ app()->getLocale() === 'ar' ? 'حفظ نوع الإجازة' : 'Save Leave Type' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
