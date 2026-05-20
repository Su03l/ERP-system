@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'فترات الرواتب' : 'Payroll Periods')
@section('content')
<div class="py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'فترات الرواتب' : 'Payroll Periods' }}</h1><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ app()->getLocale() === 'ar' ? 'إدارة فترات صرف الرواتب.' : 'Manage payroll periods.' }}</p></div>
        <a href="{{ route('payroll-periods.create') }}" class="btn-primary px-4 py-2 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>{{ app()->getLocale() === 'ar' ? 'فترة جديدة' : 'New Period' }}</a>
    </div>
    @if(session('success'))<div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 text-sm font-semibold">{{ session('success') }}</div>@endif
    <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
            <form action="{{ route('payroll-periods.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="w-40"><label class="block text-xs font-semibold text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</label>
                    <select name="status" class="erp-input w-full text-sm py-2"><option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option><option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'مفتوح' : 'Open' }}</option><option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'مغلق' : 'Closed' }}</option><option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'ملغى' : 'Cancelled' }}</option></select></div>
                <button type="submit" class="btn-secondary px-4 py-2 text-sm">{{ app()->getLocale() === 'ar' ? 'تصفية' : 'Filter' }}</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-800"><tr>
                    <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</th>
                    <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'من' : 'Start' }}</th>
                    <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'إلى' : 'End' }}</th>
                    <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'يوم الدفع' : 'Pay Date' }}</th>
                    <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                    <th class="px-6 py-4 text-right">{{ app()->getLocale() === 'ar' ? 'إجراء' : 'Action' }}</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($periods as $period)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? $period->name_ar : $period->name_en }}</td>
                            <td class="px-6 py-4 font-mono text-sm">{{ $period->starts_on->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 font-mono text-sm">{{ $period->ends_on->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 font-mono text-sm">{{ $period->pay_date->format('Y-m-d') }}</td>
                            <td class="px-6 py-4">
                                @if($period->status->value === 'open')<span class="px-2 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">{{ app()->getLocale() === 'ar' ? 'مفتوح' : 'Open' }}</span>
                                @elseif($period->status->value === 'closed')<span class="px-2 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">{{ app()->getLocale() === 'ar' ? 'مغلق' : 'Closed' }}</span>
                                @else<span class="px-2 py-1 rounded-md text-xs font-bold bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">{{ ucfirst($period->status->value) }}</span>@endif
                            </td>
                            <td class="px-6 py-4 text-right"><a href="{{ route('payroll-periods.edit', $period->id) }}" class="text-brand-600 hover:text-brand-700 font-semibold">{{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد فترات.' : 'No payroll periods.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($periods->hasPages())<div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">{{ $periods->links() }}</div>@endif
    </div>
</div>
@endsection
