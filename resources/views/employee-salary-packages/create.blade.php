@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'إنشاء حزمة راتب' : 'Create Salary Package')
@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'إنشاء حزمة راتب جديدة' : 'Create Salary Package' }}</h1>
        <a href="{{ route('employee-salary-packages.index') }}" class="btn-secondary px-4 py-2 text-sm">{{ app()->getLocale() === 'ar' ? 'عودة' : 'Back' }}</a>
    </div>
    <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl">
        <form action="{{ route('employee-salary-packages.store') }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf
            @if($errors->any())<div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 text-sm"><ul class="list-disc px-4 space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2"><label for="employee_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'الموظف' : 'Employee' }} <span class="text-rose-500">*</span></label>
                    <select name="employee_id" id="employee_id" required class="erp-input w-full"><option value="">--</option>@foreach($employees as $emp)<option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->employee_number }})</option>@endforeach</select></div>
                <div><label for="basic_salary" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'الراتب الأساسي' : 'Basic Salary' }} <span class="text-rose-500">*</span></label><input type="number" step="0.01" name="basic_salary" id="basic_salary" value="{{ old('basic_salary') }}" required class="erp-input w-full" dir="ltr"></div>
                <div><label for="housing_allowance" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'بدل السكن' : 'Housing Allowance' }}</label><input type="number" step="0.01" name="housing_allowance" id="housing_allowance" value="{{ old('housing_allowance', '0.00') }}" class="erp-input w-full" dir="ltr"></div>
                <div><label for="transportation_allowance" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'بدل النقل' : 'Transportation Allowance' }}</label><input type="number" step="0.01" name="transportation_allowance" id="transportation_allowance" value="{{ old('transportation_allowance', '0.00') }}" class="erp-input w-full" dir="ltr"></div>
                <div><label for="effective_from" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'سارية من' : 'Effective From' }} <span class="text-rose-500">*</span></label><input type="date" name="effective_from" id="effective_from" value="{{ old('effective_from') }}" required class="erp-input w-full"></div>
                <div><label for="effective_to" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'سارية حتى' : 'Effective To' }}</label><input type="date" name="effective_to" id="effective_to" value="{{ old('effective_to') }}" class="erp-input w-full"></div>
                <div><label for="status" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</label>
                    <select name="status" id="status" class="erp-input w-full"><option value="active">{{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}</option><option value="ended">{{ app()->getLocale() === 'ar' ? 'منتهي' : 'Ended' }}</option></select></div>
            </div>
            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-3">
                <a href="{{ route('employee-salary-packages.index') }}" class="btn-secondary px-6 py-2">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a>
                <button type="submit" class="btn-primary px-6 py-2">{{ app()->getLocale() === 'ar' ? 'حفظ' : 'Save' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
