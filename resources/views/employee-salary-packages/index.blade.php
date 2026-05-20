@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'حزم رواتب الموظفين' : 'Employee Salary Packages')
@section('content')
<div class="py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'حزم رواتب الموظفين' : 'Employee Salary Packages' }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ app()->getLocale() === 'ar' ? 'إدارة حزم الرواتب والبدلات الثابتة.' : 'Manage salary packages and fixed allowances.' }}</p>
        </div>
        <a href="{{ route('employee-salary-packages.create') }}" class="btn-primary px-4 py-2 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            {{ app()->getLocale() === 'ar' ? 'حزمة جديدة' : 'New Package' }}
        </a>
    </div>
    @if(session('success'))<div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 text-sm font-semibold">{{ session('success') }}</div>@endif
    <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
            <form action="{{ route('employee-salary-packages.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]"><label class="block text-xs font-semibold text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'رقم الموظف' : 'Employee ID' }}</label><input type="text" name="employee_id" value="{{ request('employee_id') }}" class="erp-input w-full text-sm py-2"></div>
                <div class="w-40"><label class="block text-xs font-semibold text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</label>
                    <select name="status" class="erp-input w-full text-sm py-2"><option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option><option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}</option><option value="ended" {{ request('status') === 'ended' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'منتهي' : 'Ended' }}</option></select></div>
                <button type="submit" class="btn-secondary px-4 py-2 text-sm">{{ app()->getLocale() === 'ar' ? 'تصفية' : 'Filter' }}</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-800"><tr>
                    <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الموظف' : 'Employee' }}</th>
                    <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الراتب الأساسي' : 'Basic Salary' }}</th>
                    <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'بدل السكن' : 'Housing' }}</th>
                    <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'بدل النقل' : 'Transport' }}</th>
                    <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'من' : 'From' }}</th>
                    <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'إلى' : 'To' }}</th>
                    <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                    <th class="px-6 py-4 text-right">{{ app()->getLocale() === 'ar' ? 'إجراء' : 'Action' }}</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($packages as $pkg)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="font-black text-slate-900 dark:text-white">{{ $pkg->employee->first_name }} {{ $pkg->employee->last_name }}</div>
                                <div class="text-[10px] text-slate-400 font-bold tracking-widest uppercase">#{{ $pkg->employee->employee_number }}</div>
                            </td>
                            <td class="px-6 py-4 font-black text-slate-900 dark:text-white">{{ number_format($pkg->basic_salary, 2) }}</td>
                            <td class="px-6 py-4 font-bold text-slate-600 dark:text-slate-400">{{ number_format($pkg->housing_allowance, 2) }}</td>
                            <td class="px-6 py-4 font-bold text-slate-600 dark:text-slate-400">{{ number_format($pkg->transportation_allowance, 2) }}</td>
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-500">{{ $pkg->effective_from?->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-500">{{ $pkg->effective_to?->format('Y-m-d') ?? '—' }}</td>
                            <td class="px-6 py-4">
                                @if($pkg->status->value === 'active')
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase bg-emerald-50 text-emerald-600 border border-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800">
                                        {{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase bg-slate-50 text-slate-600 border border-slate-100 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700">
                                        {{ $pkg->status->label() }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-end">
                                <a href="{{ route('employee-salary-packages.edit', $pkg->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-400 hover:text-brand-600 hover:border-brand-200 transition-all shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12">
                                <x-empty-state-card 
                                    :title="app()->getLocale() === 'ar' ? 'لا توجد حزم رواتب' : 'No salary packages'"
                                    :description="app()->getLocale() === 'ar' ? 'قم بتعريف حزم الرواتب للموظفين لبدء حسابات الرواتب.' : 'Define salary packages for employees to start payroll calculations.'"
                                    :actionLink="route('employee-salary-packages.create')"
                                    :actionText="app()->getLocale() === 'ar' ? 'تعريف حزمة جديدة' : 'Define New Package'"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($packages->hasPages())<div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">{{ $packages->links() }}</div>@endif
    </div>
</div>
@endsection
