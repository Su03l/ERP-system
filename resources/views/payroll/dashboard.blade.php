@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'لوحة الرواتب' : 'Payroll Dashboard')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">
                {{ app()->getLocale() === 'ar' ? 'لوحة قيادة الرواتب' : 'Payroll Dashboard' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 font-medium">
                {{ app()->getLocale() === 'ar' ? 'نظرة عامة على الرواتب، البدلات، والاستقطاعات.' : 'Overview of payrolls, allowances, and deductions.' }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('payroll-runs.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-black text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-lg shadow-brand-500/20 transition-all gap-2 active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                {{ app()->getLocale() === 'ar' ? 'تشغيل الرواتب' : 'Run Payroll' }}
            </a>
            <a href="{{ route('payroll-settings.index') }}" class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl shadow-sm transition-all gap-2 active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                {{ app()->getLocale() === 'ar' ? 'الإعدادات' : 'Settings' }}
            </a>
        </div>
    </div>

    <!-- KPI Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Total Cost -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-gradient-to-br from-brand-50 to-brand-100 dark:from-brand-900/10 dark:to-brand-800/10 rounded-bl-full -z-0 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <div class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">{{ app()->getLocale() === 'ar' ? 'إجمالي التكلفة المعتمدة' : 'Total Approved Cost' }}</div>
                <div class="text-3xl font-black text-slate-900 dark:text-white">{{ number_format($totalPayrollCost, 2) }} <span class="text-xs font-medium text-slate-400">SAR</span></div>
            </div>
        </div>
        
        <!-- Allowances -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/10 dark:to-emerald-800/10 rounded-bl-full -z-0 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <div class="text-xs font-bold text-emerald-500 uppercase tracking-widest mb-3">{{ app()->getLocale() === 'ar' ? 'إجمالي البدلات' : 'Total Allowances' }}</div>
                <div class="text-3xl font-black text-emerald-600">{{ number_format($totalAllowances, 2) }} <span class="text-xs font-medium text-slate-400">SAR</span></div>
            </div>
        </div>
        
        <!-- Deductions -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-gradient-to-br from-rose-50 to-rose-100 dark:from-rose-900/10 dark:to-rose-800/10 rounded-bl-full -z-0 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <div class="text-xs font-bold text-rose-500 uppercase tracking-widest mb-3">{{ app()->getLocale() === 'ar' ? 'إجمالي الاستقطاعات' : 'Total Deductions' }}</div>
                <div class="text-3xl font-black text-rose-600">{{ number_format($totalDeductions, 2) }} <span class="text-xs font-medium text-slate-400">SAR</span></div>
            </div>
        </div>

        <!-- Average Salary -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/10 dark:to-blue-800/10 rounded-bl-full -z-0 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <div class="text-xs font-bold text-blue-500 uppercase tracking-widest mb-3">{{ app()->getLocale() === 'ar' ? 'متوسط الراتب' : 'Average Salary' }}</div>
                <div class="text-3xl font-black text-blue-600">{{ number_format($averageSalary, 2) }} <span class="text-xs font-medium text-slate-400">SAR</span></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Latest Payroll Runs -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'أحدث تشغيلات الرواتب' : 'Latest Payroll Runs' }}</h2>
                    <a href="{{ route('payroll-runs.index') }}" class="text-xs font-black text-brand-600 hover:text-brand-700 uppercase tracking-widest bg-brand-50 px-3 py-1.5 rounded-lg border border-brand-100 transition-colors">
                        {{ app()->getLocale() === 'ar' ? 'عرض الكل' : 'View All' }}
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-6 py-4 text-start font-bold">{{ app()->getLocale() === 'ar' ? 'الفترة' : 'Period' }}</th>
                                <th class="px-6 py-4 text-center font-bold">{{ app()->getLocale() === 'ar' ? 'الموظفين' : 'Staff' }}</th>
                                <th class="px-6 py-4 text-end font-bold">{{ app()->getLocale() === 'ar' ? 'الصافي' : 'Net' }}</th>
                                <th class="px-6 py-4 text-center font-bold">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($latestRuns as $run)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors group">
                                    <td class="px-6 py-4">
                                        <a href="{{ route('payroll-runs.show', $run->id) }}" class="font-bold text-slate-900 dark:text-white hover:text-brand-600 transition-colors">
                                            {{ $run->payrollPeriod->name_ar ?? $run->payrollPeriod->name_en }}
                                        </a>
                                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">
                                            {{ $run->payrollPeriod->starts_on->format('M d') }} - {{ $run->payrollPeriod->ends_on->format('M d, Y') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="font-black text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700">
                                            {{ $run->total_employees }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-end font-black text-slate-900 dark:text-white">
                                        {{ number_format($run->net_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase border 
                                            {{ $run->status->value === 'approved' ? 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800' : '' }}
                                            {{ $run->status->value === 'pending' ? 'bg-amber-50 text-amber-600 border-amber-100 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800' : '' }}
                                            {{ $run->status->value === 'rejected' ? 'bg-rose-50 text-rose-600 border-rose-100 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800' : '' }}
                                            {{ !in_array($run->status->value, ['approved', 'pending', 'rejected']) ? 'bg-slate-50 text-slate-600 border-slate-100 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' : '' }}
                                        ">
                                            {{ $run->status->label() }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12">
                                        <x-empty-state-card 
                                            :title="app()->getLocale() === 'ar' ? 'لا توجد تشغيلات رواتب' : 'No payroll runs'"
                                            :description="app()->getLocale() === 'ar' ? 'ابدأ بتشغيل الرواتب لأول فترة متاحة.' : 'Start by generating payroll for the first available period.'"
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Links Sidebar -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden p-6">
                <h2 class="text-sm font-black text-slate-500 uppercase tracking-widest mb-6 pb-2 border-b border-slate-100 dark:border-slate-800">{{ app()->getLocale() === 'ar' ? 'إجراءات سريعة' : 'Quick Actions' }}</h2>
                <div class="grid grid-cols-1 gap-4">
                    <a href="{{ route('payroll-periods.index') }}" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all border border-transparent hover:border-slate-100 dark:hover:border-slate-800 group">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 flex items-center justify-center group-hover:scale-110 transition-transform border border-blue-100 dark:border-blue-800">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ app()->getLocale() === 'ar' ? 'فترات الرواتب' : 'Payroll Periods' }}</span>
                    </a>
                    <a href="{{ route('salary-components.index') }}" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all border border-transparent hover:border-slate-100 dark:hover:border-slate-800 group">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform border border-emerald-100 dark:border-emerald-800">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                        </div>
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ app()->getLocale() === 'ar' ? 'مكونات الراتب' : 'Salary Components' }}</span>
                    </a>
                    <a href="{{ route('employee-salary-packages.index') }}" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all border border-transparent hover:border-slate-100 dark:hover:border-slate-800 group">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 flex items-center justify-center group-hover:scale-110 transition-transform border border-indigo-100 dark:border-indigo-800">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ app()->getLocale() === 'ar' ? 'حزم رواتب الموظفين' : 'Employee Packages' }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
