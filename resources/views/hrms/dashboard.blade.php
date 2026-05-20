<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                    {{ app()->getLocale() === 'ar' ? 'لوحة الموارد البشرية' : 'HRMS Dashboard' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ app()->getLocale() === 'ar' ? 'متابعة أداء الموظفين والأقسام والمستندات.' : 'Monitor employees, departments, and documents.' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('employees.create') }}" class="btn-primary px-4 py-2 text-sm font-semibold">
                    <svg class="w-4 h-4 shrink-0 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'موظف جديد' : 'New Employee' }}</span>
                </a>
            </div>
        </div>
    </x-slot>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Employees -->
        @if(isset($resolvedData['total_employees']))
            <div class="erp-card p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">
                            {{ $resolvedData['total_employees']->label }}
                        </p>
                        <h3 class="text-3xl font-bold text-slate-900 dark:text-white">
                            {{ $resolvedData['total_employees']->value ?? 0 }}
                        </h3>
                    </div>
                    <div class="p-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                </div>
            </div>
        @endif

        <!-- Active Employees -->
        @if(isset($resolvedData['active_employees']))
            <div class="erp-card p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">
                            {{ $resolvedData['active_employees']->label }}
                        </p>
                        <h3 class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">
                            {{ $resolvedData['active_employees']->value ?? 0 }}
                        </h3>
                    </div>
                    <div class="p-2 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>
        @endif

        <!-- New Hires -->
        @if(isset($resolvedData['new_hires']))
            <div class="erp-card p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">
                            {{ $resolvedData['new_hires']->label }}
                        </p>
                        <h3 class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                            {{ $resolvedData['new_hires']->value ?? 0 }}
                        </h3>
                    </div>
                    <div class="p-2 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    </div>
                </div>
            </div>
        @endif

        <!-- Expiring Documents KPI -->
        @if(isset($resolvedData['documents_expiring_soon']))
            <div class="erp-card p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">
                            {{ $resolvedData['documents_expiring_soon']->label }}
                        </p>
                        <h3 class="text-3xl font-bold text-amber-600 dark:text-amber-400">
                            {{ $resolvedData['documents_expiring_soon']->value ?? 0 }}
                        </h3>
                    </div>
                    <div class="p-2 bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Employees by Department -->
        <div class="erp-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
            <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                    {{ app()->getLocale() === 'ar' ? 'الموظفون حسب القسم' : 'Employees by Department' }}
                </h3>
            </div>
            <div class="p-5">
                @if(isset($resolvedData['employees_by_department']) && !empty($resolvedData['employees_by_department']->metadata['values']))
                    <div class="space-y-4">
                        @foreach($resolvedData['employees_by_department']->metadata['values'] as $dept)
                            @php
                                $percentage = $resolvedData['total_employees']->value > 0 
                                    ? round(($dept['value'] / $resolvedData['total_employees']->value) * 100) 
                                    : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between items-end mb-1">
                                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $dept['label'] }}</span>
                                    <span class="text-xs font-medium text-slate-500">{{ $dept['value'] }} ({{ $percentage }}%)</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2">
                                    <div class="bg-brand-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                        {{ app()->getLocale() === 'ar' ? 'لا توجد بيانات متاحة.' : 'No data available.' }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Expiring Documents List -->
        <div class="erp-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
            <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                    {{ app()->getLocale() === 'ar' ? 'المستندات المنتهية قريباً' : 'Expiring Documents' }}
                </h3>
            </div>
            <div class="p-0">
                @if(isset($resolvedData['documents_expiring_soon']) && !empty($resolvedData['documents_expiring_soon']->metadata['documents']))
                    <ul class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach($resolvedData['documents_expiring_soon']->metadata['documents'] as $doc)
                            <li class="p-5 flex justify-between items-center hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                            {{ $doc['employee_name'] }}
                                        </p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ $doc['document_type'] ?? 'وثيقة رسمية' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                                        {{ $doc['expires_at'] }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-12">
                        <div class="w-16 h-16 rounded-full bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400">
                            {{ app()->getLocale() === 'ar' ? 'لا توجد مستندات منتهية أو على وشك الانتهاء.' : 'No documents expiring soon.' }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
