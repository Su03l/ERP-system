@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'مكونات الراتب' : 'Salary Components')
@section('content')
<div class="py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'مكونات الراتب' : 'Salary Components' }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ app()->getLocale() === 'ar' ? 'إدارة البدلات والاستقطاعات.' : 'Manage allowances and deductions.' }}</p>
        </div>
        <a href="{{ route('salary-components.create') }}" class="btn-primary px-4 py-2 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            {{ app()->getLocale() === 'ar' ? 'مكون جديد' : 'New Component' }}
        </a>
    </div>
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 text-sm font-semibold">{{ session('success') }}</div>
    @endif
    <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
            <form action="{{ route('salary-components.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]"><label class="block text-xs font-semibold text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'بحث' : 'Search' }}</label><input type="text" name="search" value="{{ request('search') }}" class="erp-input w-full text-sm py-2"></div>
                <div class="w-40"><label class="block text-xs font-semibold text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'النوع' : 'Type' }}</label>
                    <select name="type" class="erp-input w-full text-sm py-2"><option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option><option value="allowance" {{ request('type') === 'allowance' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'بدل' : 'Allowance' }}</option><option value="deduction" {{ request('type') === 'deduction' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'استقطاع' : 'Deduction' }}</option></select></div>
                <div class="w-40"><label class="block text-xs font-semibold text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</label>
                    <select name="status" class="erp-input w-full text-sm py-2"><option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option><option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}</option><option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive' }}</option></select></div>
                <button type="submit" class="btn-secondary px-4 py-2 text-sm">{{ app()->getLocale() === 'ar' ? 'تصفية' : 'Filter' }}</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</th>
                        <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الكود' : 'Code' }}</th>
                        <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'النوع' : 'Type' }}</th>
                        <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'طريقة الحساب' : 'Calc.' }}</th>
                        <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'القيمة' : 'Value' }}</th>
                        <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                        <th class="px-6 py-4 text-right">{{ app()->getLocale() === 'ar' ? 'إجراء' : 'Action' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($components as $comp)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-6 py-4"><div class="font-bold text-slate-900 dark:text-white">{{ $comp->name_ar }}</div><div class="text-xs text-slate-500">{{ $comp->name_en }}</div></td>
                            <td class="px-6 py-4 font-mono text-sm">{{ $comp->code }}</td>
                            <td class="px-6 py-4">
                                @if($comp->type->value === 'allowance')
                                    <span class="px-2 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">{{ app()->getLocale() === 'ar' ? 'بدل' : 'Allowance' }}</span>
                                @else
                                    <span class="px-2 py-1 rounded-md text-xs font-bold bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">{{ app()->getLocale() === 'ar' ? 'استقطاع' : 'Deduction' }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($comp->calculation_type->value === 'fixed')
                                    <span class="px-2 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">{{ app()->getLocale() === 'ar' ? 'ثابت' : 'Fixed' }}</span>
                                @else
                                    <span class="px-2 py-1 rounded-md text-xs font-bold bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">{{ app()->getLocale() === 'ar' ? 'نسبة' : 'Percentage' }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-700 dark:text-slate-300">
                                {{ $comp->calculation_type->value === 'fixed' ? number_format($comp->default_amount, 2) : $comp->default_percentage . '%' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($comp->status->value === 'active')
                                    <span class="px-2 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">{{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}</span>
                                @else
                                    <span class="px-2 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">{{ app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive' }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right"><a href="{{ route('salary-components.edit', $comp->id) }}" class="text-brand-600 hover:text-brand-700 font-semibold">{{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-12 text-center text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد مكونات.' : 'No salary components.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($components->hasPages())<div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">{{ $components->links() }}</div>@endif
    </div>
</div>
@endsection
