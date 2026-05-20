<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                    {{ app()->getLocale() === 'ar' ? 'لوحة مؤشرات الحضور' : 'Attendance Dashboard' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ app()->getLocale() === 'ar' ? 'نظرة عامة على معدلات الحضور والغياب والإجازات.' : 'Overview of attendance rates, absences, and leaves.' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('attendance-records.index') }}" class="btn-secondary px-4 py-2 text-sm font-semibold">
                    {{ app()->getLocale() === 'ar' ? 'سجلات الحضور' : 'Attendance Records' }}
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Date Filters (Placeholder) -->
    <div class="erp-card p-4 mb-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl flex flex-wrap items-center justify-between gap-4">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            {{ app()->getLocale() === 'ar' ? 'الفترة:' : 'Period:' }} 
            <span class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'هذا الشهر' : 'This Month' }}</span>
        </h2>
        <div class="flex gap-2">
            <input type="date" class="erp-input text-sm py-1.5" disabled>
            <input type="date" class="erp-input text-sm py-1.5" disabled>
            <button class="btn-secondary text-sm px-3 py-1.5" disabled>{{ app()->getLocale() === 'ar' ? 'تحديث' : 'Update' }}</button>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Attendance Rate -->
        <div class="erp-card bg-white dark:bg-slate-900 p-6 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-16 h-16 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <p class="text-sm font-bold text-slate-500 dark:text-slate-400 mb-1 relative z-10">{{ app()->getLocale() === 'ar' ? 'معدل الحضور' : 'Attendance Rate' }}</p>
            <h3 class="text-3xl font-black text-slate-900 dark:text-white relative z-10 flex items-baseline gap-2">
                {{ $kpis['hr.attendance_rate']['value'] ?? '92' }}%
            </h3>
            <p class="text-xs font-medium text-emerald-600 mt-2 relative z-10 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                2% {{ app()->getLocale() === 'ar' ? 'مقارنة بالشهر السابق' : 'vs last month' }}
            </p>
        </div>

        <!-- Total Absences -->
        <div class="erp-card bg-white dark:bg-slate-900 p-6 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-16 h-16 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <p class="text-sm font-bold text-slate-500 dark:text-slate-400 mb-1 relative z-10">{{ app()->getLocale() === 'ar' ? 'أيام الغياب' : 'Total Absences' }}</p>
            <h3 class="text-3xl font-black text-slate-900 dark:text-white relative z-10 flex items-baseline gap-2">
                {{ $kpis['hr.total_absences']['value'] ?? '14' }}
            </h3>
            <p class="text-xs font-medium text-red-600 mt-2 relative z-10 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                5% {{ app()->getLocale() === 'ar' ? 'زيادة عن الشهر السابق' : 'higher than last month' }}
            </p>
        </div>

        <!-- Average Overtime -->
        <div class="erp-card bg-white dark:bg-slate-900 p-6 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-16 h-16 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <p class="text-sm font-bold text-slate-500 dark:text-slate-400 mb-1 relative z-10">{{ app()->getLocale() === 'ar' ? 'متوسط العمل الإضافي' : 'Avg. Overtime (hrs)' }}</p>
            <h3 class="text-3xl font-black text-slate-900 dark:text-white relative z-10 flex items-baseline gap-2">
                {{ $kpis['hr.average_overtime']['value'] ?? '3.5' }}
            </h3>
            <p class="text-xs font-medium text-slate-500 mt-2 relative z-10 flex items-center gap-1">
                {{ app()->getLocale() === 'ar' ? 'لكل موظف أسبوعياً' : 'per employee per week' }}
            </p>
        </div>
    </div>

    <!-- Charts / Tables Placeholder -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Attendance Trend Chart Placeholder -->
        <div class="erp-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'اتجاه الحضور' : 'Attendance Trend' }}</h3>
            </div>
            <div class="h-64 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-dashed border-slate-200 dark:border-slate-700 flex items-center justify-center">
                <p class="text-slate-400 text-sm">{{ app()->getLocale() === 'ar' ? 'رسم بياني يوضح معدلات الحضور اليومية...' : 'Chart displaying daily attendance rates...' }}</p>
            </div>
        </div>

        <!-- Department Absences Placeholder -->
        <div class="erp-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'الغياب حسب القسم' : 'Absences by Department' }}</h3>
            </div>
            <div class="space-y-4">
                <!-- Progress Bar Item -->
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ app()->getLocale() === 'ar' ? 'المبيعات' : 'Sales' }}</span>
                        <span class="text-slate-500 text-xs">8 {{ app()->getLocale() === 'ar' ? 'أيام' : 'days' }}</span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                        <div class="bg-red-500 h-2 rounded-full" style="width: 45%"></div>
                    </div>
                </div>
                <!-- Progress Bar Item -->
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ app()->getLocale() === 'ar' ? 'تقنية المعلومات' : 'IT' }}</span>
                        <span class="text-slate-500 text-xs">4 {{ app()->getLocale() === 'ar' ? 'أيام' : 'days' }}</span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                        <div class="bg-red-400 h-2 rounded-full" style="width: 25%"></div>
                    </div>
                </div>
                <!-- Progress Bar Item -->
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ app()->getLocale() === 'ar' ? 'الموارد البشرية' : 'HR' }}</span>
                        <span class="text-slate-500 text-xs">2 {{ app()->getLocale() === 'ar' ? 'أيام' : 'days' }}</span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                        <div class="bg-red-300 h-2 rounded-full" style="width: 10%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
