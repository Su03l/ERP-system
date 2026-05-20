<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                    {{ app()->getLocale() === 'ar' ? 'صندوق الوارد الموحد للاعتمادات' : 'Unified Approval Inbox' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ app()->getLocale() === 'ar' ? 'مراجعة واعتماد طلبات الإجازات، مسيرات الرواتب، القيود المحاسبية، وتفويضات النظام.' : 'Review and authorize pending leave requests, payroll runs, journal entries, and workflow tasks.' }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if(session('success'))
            <div class="p-4 bg-teal-50 dark:bg-teal-950/20 border border-teal-200 dark:border-teal-900 rounded-xl text-teal-800 dark:text-teal-400 font-medium text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900 rounded-xl text-rose-800 dark:text-rose-400 font-medium text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Filter Panel -->
        <div class="erp-card p-4 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <!-- Module Tabs -->
                <div class="flex flex-wrap gap-1.5 bg-slate-100 dark:bg-slate-800/80 p-1 rounded-xl">
                    <a href="{{ route('approvals.index', ['status' => $status]) }}" 
                       class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {{ !$moduleKey ? 'bg-white dark:bg-slate-900 text-brand-600 dark:text-brand-400 shadow-sm' : 'text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                        {{ app()->getLocale() === 'ar' ? 'الكل' : 'All Modules' }}
                    </a>
                    <a href="{{ route('approvals.index', ['status' => $status, 'module_key' => 'leave']) }}" 
                       class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $moduleKey === 'leave' ? 'bg-white dark:bg-slate-900 text-brand-600 dark:text-brand-400 shadow-sm' : 'text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                        {{ app()->getLocale() === 'ar' ? 'الإجازات' : 'Leaves' }}
                    </a>
                    <a href="{{ route('approvals.index', ['status' => $status, 'module_key' => 'payroll']) }}" 
                       class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $moduleKey === 'payroll' ? 'bg-white dark:bg-slate-900 text-brand-600 dark:text-brand-400 shadow-sm' : 'text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                        {{ app()->getLocale() === 'ar' ? 'الرواتب' : 'Payroll' }}
                    </a>
                    <a href="{{ route('approvals.index', ['status' => $status, 'module_key' => 'accounting']) }}" 
                       class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $moduleKey === 'accounting' ? 'bg-white dark:bg-slate-900 text-brand-600 dark:text-brand-400 shadow-sm' : 'text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                        {{ app()->getLocale() === 'ar' ? 'القيود المحاسبية' : 'Journal Entries' }}
                    </a>
                    <a href="{{ route('approvals.index', ['status' => $status, 'module_key' => 'assets']) }}" 
                       class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $moduleKey === 'assets' ? 'bg-white dark:bg-slate-900 text-brand-600 dark:text-brand-400 shadow-sm' : 'text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                        {{ app()->getLocale() === 'ar' ? 'الأصول والعهد' : 'Assets Custody' }}
                    </a>
                    <a href="{{ route('approvals.index', ['status' => $status, 'module_key' => 'projects']) }}" 
                       class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer {{ $moduleKey === 'projects' ? 'bg-white dark:bg-slate-900 text-brand-600 dark:text-brand-400 shadow-sm' : 'text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                        {{ app()->getLocale() === 'ar' ? 'المشاريع والمهام' : 'Projects' }}
                    </a>
                </div>

                <!-- Status Filter -->
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-450">{{ app()->getLocale() === 'ar' ? 'حالة الاعتماد:' : 'Approval Status:' }}</span>
                    <div class="flex gap-1">
                        <a href="{{ route('approvals.index', ['status' => 'pending', 'module_key' => $moduleKey]) }}" 
                           class="px-2.5 py-1 rounded text-xs font-bold {{ $status === 'pending' ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/30 dark:text-brand-400 border border-brand-200 dark:border-brand-900/60' : 'bg-slate-50 dark:bg-slate-800/40 text-slate-600 border border-slate-200/55 dark:border-slate-700 hover:bg-slate-100' }}">
                            {{ app()->getLocale() === 'ar' ? 'تحت الإجراء' : 'Pending' }}
                        </a>
                        <a href="{{ route('approvals.index', ['status' => 'completed', 'module_key' => $moduleKey]) }}" 
                           class="px-2.5 py-1 rounded text-xs font-bold {{ $status === 'completed' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/60' : 'bg-slate-50 dark:bg-slate-800/40 text-slate-600 border border-slate-200/55 dark:border-slate-700 hover:bg-slate-100' }}">
                            {{ app()->getLocale() === 'ar' ? 'معتمد' : 'Approved' }}
                        </a>
                        <a href="{{ route('approvals.index', ['status' => 'rejected', 'module_key' => $moduleKey]) }}" 
                           class="px-2.5 py-1 rounded text-xs font-bold {{ $status === 'rejected' ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400 border border-rose-200 dark:border-rose-900/60' : 'bg-slate-50 dark:bg-slate-800/40 text-slate-600 border border-slate-200/55 dark:border-slate-700 hover:bg-slate-100' }}">
                            {{ app()->getLocale() === 'ar' ? 'مرفوض' : 'Rejected' }}
                        </a>
                        <a href="{{ route('approvals.index', ['status' => 'returned', 'module_key' => $moduleKey]) }}" 
                           class="px-2.5 py-1 rounded text-xs font-bold {{ $status === 'returned' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400 border border-amber-200 dark:border-amber-900/60' : 'bg-slate-50 dark:bg-slate-800/40 text-slate-600 border border-slate-200/55 dark:border-slate-700 hover:bg-slate-100' }}">
                            {{ app()->getLocale() === 'ar' ? 'معاد للمراجعة' : 'Returned' }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approvals List Table -->
        <div class="erp-card p-6">
            <div class="erp-table-container">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>{{ app()->getLocale() === 'ar' ? 'معاملة التدفق' : 'Approval Instance' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'نوع الوحدة' : 'Module / Topic' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'مقدم الطلب' : 'Requested By' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'المرحلة الحالية' : 'Current Step / Role' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'تاريخ الطلب' : 'Request Date' }}</th>
                            <th class="text-center">{{ app()->getLocale() === 'ar' ? 'حالة الطلب' : 'Status' }}</th>
                            <th class="text-center">{{ app()->getLocale() === 'ar' ? 'خيارات' : 'Options' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($instances as $instance)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold flex items-center justify-center shrink-0">
                                            @if($instance->workflow && $instance->workflow->module_key === 'leave')
                                                <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            @elseif($instance->workflow && $instance->workflow->module_key === 'payroll')
                                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            @elseif($instance->workflow && $instance->workflow->module_key === 'accounting')
                                                <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                            @else
                                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                            @endif
                                        </div>
                                        <div>
                                            <a href="{{ route('approvals.show', $instance->id) }}" class="font-bold text-slate-800 dark:text-white hover:text-brand-600 dark:hover:text-brand-400 transition-colors block leading-tight">
                                                {{ $instance->workflow ? $instance->workflow->name : __('common.workflow') }}
                                            </a>
                                            <span class="text-[10px] text-slate-400 block mt-0.5 font-mono">ID: #{{ $instance->id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $moduleLabel = match($instance->workflow?->module_key) {
                                            'leave' => app()->getLocale() === 'ar' ? 'طلب إجازة' : 'Leave Request',
                                            'payroll' => app()->getLocale() === 'ar' ? 'مسير الرواتب' : 'Payroll Run',
                                            'accounting' => app()->getLocale() === 'ar' ? 'قيود المحاسبة' : 'Journal Entry',
                                            'assets' => app()->getLocale() === 'ar' ? 'عهد الأصول' : 'Asset Custody',
                                            'projects' => app()->getLocale() === 'ar' ? 'مهام المشاريع' : 'Projects / Tasks',
                                            default => $instance->workflow?->module_key ?: 'نظامي'
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-450 border border-slate-200 dark:border-slate-700 rounded text-[10px] font-bold">
                                        {{ $moduleLabel }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-brand-50 dark:bg-brand-950/20 text-brand-600 dark:text-brand-400 text-[10px] font-bold flex items-center justify-center shrink-0">
                                            {{ $instance->requestedBy ? mb_strtoupper(mb_substr($instance->requestedBy->name, 0, 2)) : '—' }}
                                        </div>
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-350">
                                            {{ $instance->requestedBy ? $instance->requestedBy->name : '—' }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    @if($instance->currentStep)
                                        <div class="space-y-0.5">
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                                {{ $instance->currentStep->name }}
                                            </span>
                                            <span class="block text-[9px] text-slate-400 uppercase leading-none">
                                                {{ app()->getLocale() === 'ar' ? 'الخطوة رقم' : 'Step' }} #{{ $instance->currentStep->order }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="text-xs font-mono text-slate-500 dark:text-slate-450">
                                    {{ $instance->created_at ? $instance->created_at->format('Y-m-d H:i') : '—' }}
                                </td>
                                <td class="text-center">
                                    @if($instance->status === 'pending')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-brand-50 dark:bg-brand-950/20 border border-brand-200/50 dark:border-brand-900/50 rounded-full text-brand-700 dark:text-brand-400 font-bold text-[10px]">
                                            <span class="w-1 h-1 rounded-full bg-brand-500 animate-pulse"></span>
                                            <span>{{ app()->getLocale() === 'ar' ? 'تحت المراجعة' : 'Pending' }}</span>
                                        </span>
                                    @elseif($instance->status === 'completed')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200/50 dark:border-emerald-900/50 rounded-full text-emerald-700 dark:text-emerald-400 font-bold text-[10px]">
                                            <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                                            <span>{{ app()->getLocale() === 'ar' ? 'مكتمل ومعتمد' : 'Approved' }}</span>
                                        </span>
                                    @elseif($instance->status === 'rejected')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-rose-50 dark:bg-rose-950/20 border border-rose-200/50 dark:border-rose-900/50 rounded-full text-rose-700 dark:text-rose-400 font-bold text-[10px]">
                                            <span class="w-1 h-1 rounded-full bg-rose-500"></span>
                                            <span>{{ app()->getLocale() === 'ar' ? 'مرفوض' : 'Rejected' }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-50 dark:bg-amber-950/20 border border-amber-200/50 dark:border-amber-900/50 rounded-full text-amber-700 dark:text-amber-400 font-bold text-[10px]">
                                            <span class="w-1 h-1 rounded-full bg-amber-500"></span>
                                            <span>{{ app()->getLocale() === 'ar' ? 'معاد للمراجع' : 'Returned' }}</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('approvals.show', $instance->id) }}" class="p-2 bg-brand-50 hover:bg-brand-100 dark:bg-brand-950/30 dark:hover:bg-brand-950/60 text-brand-600 dark:text-brand-400 text-xs font-bold rounded-lg transition-colors cursor-pointer inline-flex items-center justify-center">
                                        {{ app()->getLocale() === 'ar' ? 'فتح والمعالجة' : 'Open & Process' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-16 text-slate-400">
                                    <div class="flex flex-col items-center justify-center space-y-3">
                                        <div class="w-12 h-12 bg-slate-50 dark:bg-slate-800/30 rounded-full flex items-center justify-center mx-auto text-slate-350">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                        </div>
                                        <p class="text-sm font-bold text-slate-550">{{ app()->getLocale() === 'ar' ? 'لا توجد طلبات معلقة بانتظار اعتمادك حالياً.' : 'Your inbox is clear! No pending tasks require your action.' }}</p>
                                        <p class="text-xs text-slate-400 max-w-sm mx-auto leading-normal">{{ app()->getLocale() === 'ar' ? 'عندما يتم إسناد خطوات سير عمل جديدة إلى حسابك أو لأدوارك الوظيفية، ستظهر هنا فوراً.' : 'Any future tasks assigned to your email, user groups, or authorization levels will immediately appear here.' }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
