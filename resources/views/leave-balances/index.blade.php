@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'أرصدة الإجازات' : 'Leave Balances')

@section('content')
<div class="py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                {{ app()->getLocale() === 'ar' ? 'أرصدة الإجازات' : 'Leave Balances' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ app()->getLocale() === 'ar' ? 'متابعة أرصدة الإجازات للموظفين وتحديثها إن لزم.' : 'Monitor employee leave balances and adjust if necessary.' }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('leave.dashboard') }}" class="btn-secondary px-4 py-2">
                {{ app()->getLocale() === 'ar' ? 'لوحة الإجازات' : 'Leave Dashboard' }}
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 text-sm font-semibold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
        
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
            <form action="{{ route('leave-balances.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ app()->getLocale() === 'ar' ? 'رقم الموظف' : 'Employee ID' }}</label>
                    <input type="text" name="employee_id" value="{{ request('employee_id') }}" class="erp-input w-full text-sm py-2">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ app()->getLocale() === 'ar' ? 'نوع الإجازة' : 'Leave Type ID' }}</label>
                    <input type="text" name="leave_type_id" value="{{ request('leave_type_id') }}" class="erp-input w-full text-sm py-2">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ app()->getLocale() === 'ar' ? 'السنة' : 'Year' }}</label>
                    <input type="number" name="year" value="{{ request('year') }}" class="erp-input w-full text-sm py-2" placeholder="e.g. 2024">
                </div>
                <div>
                    <button type="submit" class="btn-secondary px-4 py-2 w-full text-sm">
                        {{ app()->getLocale() === 'ar' ? 'تصفية' : 'Filter' }}
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الموظف' : 'Employee' }}</th>
                        <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'نوع الإجازة' : 'Leave Type' }}</th>
                        <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'السنة' : 'Year' }}</th>
                        <th class="px-6 py-4 text-center">{{ app()->getLocale() === 'ar' ? 'الرصيد الإجمالي' : 'Total Days' }}</th>
                        <th class="px-6 py-4 text-center">{{ app()->getLocale() === 'ar' ? 'المستخدم' : 'Used Days' }}</th>
                        <th class="px-6 py-4 text-center">{{ app()->getLocale() === 'ar' ? 'المتبقي' : 'Remaining' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($leaveBalances as $balance)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $balance->employee->first_name }} {{ $balance->employee->last_name }}</div>
                                <div class="text-xs text-slate-500">{{ $balance->employee->employee_number }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 dark:text-slate-200">
                                    {{ app()->getLocale() === 'ar' ? $balance->leaveType->name_ar : $balance->leaveType->name_en }}
                                </div>
                            </td>
                            <td class="px-6 py-4 font-mono text-sm">{{ $balance->year }}</td>
                            <td class="px-6 py-4 text-center font-semibold text-slate-700 dark:text-slate-300">
                                {{ floatval($balance->total_days) }}
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-rose-600">
                                {{ floatval($balance->used_days) }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-emerald-600">
                                {{ floatval($balance->total_days) - floatval($balance->used_days) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                {{ app()->getLocale() === 'ar' ? 'لا توجد أرصدة تطابق هذا البحث.' : 'No balances match your search.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($leaveBalances->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">
                {{ $leaveBalances->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
