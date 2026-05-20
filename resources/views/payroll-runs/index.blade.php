@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تشغيلات الرواتب' : 'Payroll Runs')
@section('content')
<div class="py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'تشغيلات الرواتب' : 'Payroll Runs' }}</h1><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ app()->getLocale() === 'ar' ? 'عرض وإدارة تشغيلات الرواتب.' : 'View and manage payroll runs.' }}</p></div>
        <a href="{{ route('payroll-runs.create') }}" class="btn-primary px-4 py-2 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>{{ app()->getLocale() === 'ar' ? 'تشغيل الرواتب' : 'Generate Payroll' }}</a>
    </div>
    @if(session('success'))<div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 text-sm font-semibold">{{ session('success') }}</div>@endif
    <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
            <form action="{{ route('payroll-runs.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="w-40"><label class="block text-xs font-semibold text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</label>
                    <select name="status" class="erp-input w-full text-sm py-2"><option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option><option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'مسودة' : 'Draft' }}</option><option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'معلق' : 'Pending' }}</option><option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'معتمد' : 'Approved' }}</option><option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'مرفوض' : 'Rejected' }}</option></select></div>
                <button type="submit" class="btn-secondary px-4 py-2 text-sm">{{ app()->getLocale() === 'ar' ? 'تصفية' : 'Filter' }}</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-800"><tr>
                    <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'رقم التشغيل' : 'Run #' }}</th>
                    <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الفترة' : 'Period' }}</th>
                    <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                    <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الموظفون' : 'Employees' }}</th>
                    <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الإجمالي' : 'Gross' }}</th>
                    <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الاستقطاعات' : 'Deductions' }}</th>
                    <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الصافي' : 'Net' }}</th>
                    <th class="px-6 py-4 text-right">{{ app()->getLocale() === 'ar' ? 'إجراء' : 'Action' }}</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($runs as $run)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">{{ $run->run_number }}</td>
                            <td class="px-6 py-4"><div class="font-semibold">{{ $run->payrollPeriod->name_ar ?? $run->payrollPeriod->name_en }}</div><div class="text-xs text-slate-500 font-mono">{{ $run->payrollPeriod->starts_on->format('M d') }} - {{ $run->payrollPeriod->ends_on->format('M d, Y') }}</div></td>
                            <td class="px-6 py-4">
                                @if($run->status->value === 'approved' || $run->status->value === 'paid')<span class="px-2 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">{{ ucfirst($run->status->value) }}</span>
                                @elseif($run->status->value === 'pending')<span class="px-2 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">{{ app()->getLocale() === 'ar' ? 'معلق' : 'Pending' }}</span>
                                @elseif($run->status->value === 'rejected')<span class="px-2 py-1 rounded-md text-xs font-bold bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">{{ app()->getLocale() === 'ar' ? 'مرفوض' : 'Rejected' }}</span>
                                @else<span class="px-2 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">{{ ucfirst($run->status->value) }}</span>@endif
                            </td>
                            <td class="px-6 py-4 font-semibold">{{ $run->total_employees }}</td>
                            <td class="px-6 py-4 font-mono">{{ number_format($run->gross_amount, 2) }}</td>
                            <td class="px-6 py-4 font-mono text-rose-600">{{ number_format($run->total_deductions, 2) }}</td>
                            <td class="px-6 py-4 font-extrabold text-slate-900 dark:text-white">{{ number_format($run->net_amount, 2) }}</td>
                            <td class="px-6 py-4 text-right"><a href="{{ route('payroll-runs.show', $run->id) }}" class="text-brand-600 hover:text-brand-700 font-semibold">{{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-6 py-12 text-center text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد تشغيلات.' : 'No payroll runs yet.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($runs->hasPages())<div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">{{ $runs->links() }}</div>@endif
    </div>
</div>
@endsection
