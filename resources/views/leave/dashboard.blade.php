@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'لوحة مؤشرات الإجازات' : 'Leave Dashboard')

@section('content')
<div class="py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                {{ app()->getLocale() === 'ar' ? 'لوحة الإجازات' : 'Leave Dashboard' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ app()->getLocale() === 'ar' ? 'نظرة عامة على الإجازات والطلبات المعلقة.' : 'Overview of leaves and pending requests.' }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('leave-requests.index') }}" class="btn-secondary px-4 py-2">
                {{ app()->getLocale() === 'ar' ? 'سجل الطلبات' : 'Requests Log' }}
            </a>
            <a href="{{ route('leave-requests.create') }}" class="btn-primary px-4 py-2 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                {{ app()->getLocale() === 'ar' ? 'طلب إجازة جديد' : 'New Leave Request' }}
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @foreach($kpis as $kpi)
            <div class="erp-card p-6 flex flex-col relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-brand-50 dark:bg-brand-900/20 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out"></div>
                <div class="relative z-10 flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        {{ $kpi->name }}
                    </h3>
                    <div class="w-10 h-10 rounded-xl bg-brand-100 dark:bg-brand-900/40 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0 shadow-inner">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                </div>
                <div class="relative z-10 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ $kpi->formatted_value }}
                    </span>
                    @if($kpi->trend)
                        <span class="text-sm font-semibold {{ $kpi->trend === 'up' ? 'text-emerald-600' : 'text-rose-600' }} flex items-center">
                            @if($kpi->trend === 'up')
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            @else
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                            @endif
                            {{ $kpi->trend_percentage }}%
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pending Requests and Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Pending Leave Requests -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'الطلبات المعلقة' : 'Pending Requests' }}</h2>
                <a href="{{ route('leave-requests.index', ['status' => 'pending']) }}" class="text-sm font-semibold text-brand-600 dark:text-brand-400 hover:text-brand-700">
                    {{ app()->getLocale() === 'ar' ? 'عرض الكل' : 'View All' }}
                </a>
            </div>
            
            <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                @if($pendingRequests->isEmpty())
                    <div class="p-8 text-center">
                        <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-1">{{ app()->getLocale() === 'ar' ? 'لا توجد طلبات معلقة' : 'No Pending Requests' }}</h3>
                        <p class="text-xs text-slate-500">{{ app()->getLocale() === 'ar' ? 'جميع الطلبات تمت معالجتها بنجاح.' : 'All leave requests have been processed.' }}</p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($pendingRequests as $request)
                            <div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors flex items-center justify-between group">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 font-bold shrink-0">
                                        {{ mb_substr($request->employee->first_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $request->employee->first_name }} {{ $request->employee->last_name }}</div>
                                        <div class="text-xs text-slate-500 flex items-center gap-2 mt-0.5">
                                            <span>{{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d, Y') }}</span>
                                            <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                            <span class="font-medium text-slate-600 dark:text-slate-400">{{ $request->total_days }} {{ app()->getLocale() === 'ar' ? 'أيام' : 'Days' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <a href="{{ route('leave-requests.show', $request->id) }}" class="btn-secondary px-3 py-1.5 text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                                        {{ app()->getLocale() === 'ar' ? 'مراجعة' : 'Review' }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Links / Summary -->
        <div class="space-y-4">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'إدارة الإجازات' : 'Leave Management' }}</h2>
            
            <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl p-2">
                <a href="{{ route('leave-balances.index') }}" class="flex items-center gap-4 p-3 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">{{ app()->getLocale() === 'ar' ? 'أرصدة الإجازات' : 'Leave Balances' }}</div>
                        <div class="text-xs text-slate-500">{{ app()->getLocale() === 'ar' ? 'عرض أرصدة الإجازات للموظفين' : 'View employee leave balances' }}</div>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
                
                <a href="{{ route('leave-types.index') }}" class="flex items-center gap-4 p-3 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group mt-1">
                    <div class="w-10 h-10 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">{{ app()->getLocale() === 'ar' ? 'أنواع الإجازات' : 'Leave Types' }}</div>
                        <div class="text-xs text-slate-500">{{ app()->getLocale() === 'ar' ? 'إدارة سياسات الإجازات' : 'Manage leave policies' }}</div>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
