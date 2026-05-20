<x-app-layout>
    <!-- Dynamic Header -->
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
            {{ app()->getLocale() === 'ar' ? 'لوحة التحكم والمؤشرات' : 'Dashboard & Analytics' }}
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            {{ app()->getLocale() === 'ar' ? 'متابعة أداء الأقسام، الموظفين، والعمليات المالية والتشغيلية.' : 'Monitor department performance, employees, and financial operations.' }}
        </p>
    </x-slot>

    <!-- Slot for actions (Quick Actions) -->
    <x-slot name="actions">
        <button class="btn-secondary">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            <span>{{ app()->getLocale() === 'ar' ? 'تصدير التقارير' : 'Export Reports' }}</span>
        </button>
        <button class="btn-primary">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            <span>{{ app()->getLocale() === 'ar' ? 'إجراء سريع' : 'Quick Action' }}</span>
        </button>
    </x-slot>

    <!-- KPI Grid (SaaS Metrics) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1: Total Employees -->
        <div class="erp-card p-6 flex items-center justify-between">
            <div class="space-y-2">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    {{ app()->getLocale() === 'ar' ? 'إجمالي الموظفين' : 'Total Employees' }}
                </span>
                <h3 class="text-3xl font-bold text-slate-800 dark:text-white">124</h3>
                <span class="inline-flex items-center text-xs font-semibold text-emerald-500 bg-emerald-50 dark:bg-emerald-950/20 px-2 py-0.5 rounded-full">
                    +4.8% {{ app()->getLocale() === 'ar' ? 'الشهر الحالي' : 'this month' }}
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-teal-50 dark:bg-teal-950/20 text-brand-600 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>

        <!-- Card 2: Operating Cash Balance -->
        <div class="erp-card p-6 flex items-center justify-between">
            <div class="space-y-2">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    {{ app()->getLocale() === 'ar' ? 'رصيد التشغيل' : 'Operating Cash' }}
                </span>
                <h3 class="text-3xl font-bold text-slate-800 dark:text-white">
                    {{ app()->getLocale() === 'ar' ? '450,230 ر.س' : 'SAR 450,230' }}
                </h3>
                <span class="inline-flex items-center text-xs font-semibold text-emerald-500 bg-emerald-50 dark:bg-emerald-950/20 px-2 py-0.5 rounded-full">
                    +12.4% {{ app()->getLocale() === 'ar' ? 'الربع الحالي' : 'this quarter' }}
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-teal-50 dark:bg-teal-950/20 text-brand-600 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            </div>
        </div>

        <!-- Card 3: Active Projects -->
        <div class="erp-card p-6 flex items-center justify-between">
            <div class="space-y-2">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    {{ app()->getLocale() === 'ar' ? 'المشاريع النشطة' : 'Active Projects' }}
                </span>
                <h3 class="text-3xl font-bold text-slate-800 dark:text-white">12</h3>
                <span class="inline-flex items-center text-xs font-semibold text-slate-500 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full">
                    3 {{ app()->getLocale() === 'ar' ? 'معلقة' : 'pending' }}
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-teal-50 dark:bg-teal-950/20 text-brand-600 dark:text-emerald-400 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
            </div>
        </div>

        <!-- Card 4: Approvals Pending -->
        <div class="erp-card p-6 flex items-center justify-between">
            <div class="space-y-2">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    {{ app()->getLocale() === 'ar' ? 'طلبات بانتظار الاعتماد' : 'Pending Approvals' }}
                </span>
                <h3 class="text-3xl font-bold text-slate-800 dark:text-white">5</h3>
                <span class="inline-flex items-center text-xs font-semibold text-rose-500 bg-rose-50 dark:bg-rose-950/20 px-2 py-0.5 rounded-full">
                    {{ app()->getLocale() === 'ar' ? 'تحتاج إجراء عاجل' : 'Action Required' }}
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            </div>
        </div>
    </div>

    <!-- Details Section (Dynamic list grids) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Section 1: Recent Activity / Logs Table (2 columns width) -->
        <div class="erp-card p-6 lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="font-bold text-slate-800 dark:text-white">
                    {{ app()->getLocale() === 'ar' ? 'آخر العمليات والتسجيلات' : 'Recent Activities' }}
                </h4>
                <a href="#" class="text-xs font-semibold text-brand-500 hover:text-brand-600 transition-colors">
                    {{ app()->getLocale() === 'ar' ? 'عرض الكل' : 'View All' }}
                </a>
            </div>

            <!-- Standard ERP table -->
            <div class="erp-table-container">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>{{ app()->getLocale() === 'ar' ? 'الموظف' : 'Employee' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'نوع الإجراء' : 'Activity' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="font-medium text-slate-800 dark:text-white">أحمد المحمد</td>
                            <td>تقنية المعلومات</td>
                            <td>تسجيل حضور (08:02 ص)</td>
                            <td><span class="erp-badge erp-badge-success">{{ app()->getLocale() === 'ar' ? 'منتظم' : 'On Time' }}</span></td>
                        </tr>
                        <tr>
                            <td class="font-medium text-slate-800 dark:text-white">خالد العتيبي</td>
                            <td>المالية والرواتب</td>
                            <td>طلب إجازة سنوية (5 أيام)</td>
                            <td><span class="erp-badge erp-badge-warning">{{ app()->getLocale() === 'ar' ? 'معلق' : 'Pending' }}</span></td>
                        </tr>
                        <tr>
                            <td class="font-medium text-slate-800 dark:text-white">سارة الشمري</td>
                            <td>الموارد البشرية</td>
                            <td>تسجيل انصراف (04:55 م)</td>
                            <td><span class="erp-badge erp-badge-success">{{ app()->getLocale() === 'ar' ? 'مكتمل' : 'Completed' }}</span></td>
                        </tr>
                        <tr>
                            <td class="font-medium text-slate-800 dark:text-white">عبدالعزيز القحطاني</td>
                            <td>المبيعات والعملاء</td>
                            <td>طلب سلفة استثنائية</td>
                            <td><span class="erp-badge erp-badge-danger">{{ app()->getLocale() === 'ar' ? 'مرفوض' : 'Rejected' }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 2: Quick Action / System Context Panel -->
        <div class="erp-card p-6 space-y-6">
            <h4 class="font-bold text-slate-800 dark:text-white">
                {{ app()->getLocale() === 'ar' ? 'تحديثات وحالة النظام' : 'System Overview' }}
            </h4>

            <div class="space-y-4">
                <!-- HR Block -->
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-950/20 text-brand-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h5 class="text-sm font-semibold text-slate-800 dark:text-white">
                            {{ app()->getLocale() === 'ar' ? 'مسيرة الرواتب جاهزة' : 'Payroll Run Ready' }}
                        </h5>
                        <p class="text-xs text-slate-400 mt-1">
                            {{ app()->getLocale() === 'ar' ? 'تم الانتهاء من مراجعة ساعات حضور شهر مايو لجميع الموظفين.' : 'May attendance logs have been verified for all departments.' }}
                        </p>
                    </div>
                </div>

                <!-- Accounting Block -->
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-950/20 text-brand-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h5 class="text-sm font-semibold text-slate-800 dark:text-white">
                            {{ app()->getLocale() === 'ar' ? 'أرصدة حسابات غير متطابقة' : 'Unbalanced Journal Entries' }}
                        </h5>
                        <p class="text-xs text-slate-400 mt-1">
                            {{ app()->getLocale() === 'ar' ? 'هناك قيود يومية معلقة تحتاج لمطابقة الأرصدة المدنية والدائنة.' : 'There are draft entries that require balance matching.' }}
                        </p>
                    </div>
                </div>

                <!-- CRM Block -->
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-950/20 text-brand-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 10.742l3.6-1.8A1 1 0 0013 8V4a1 1 0 00-1-1H4a1 1 0 00-1 1v8a1 1 0 001 1h4a1 1 0 00.684-.258l3.6 1.8A1 1 0 0013 14v4a1 1 0 00-1 1H4a1 1 0 00-1-1v-8a1 1 0 001-1h4a1 1 0 00.684.258z"></path></svg>
                    </div>
                    <div>
                        <h5 class="text-sm font-semibold text-slate-800 dark:text-white">
                            {{ app()->getLocale() === 'ar' ? 'فرص مبيعات جديدة' : 'New CRM Leads' }}
                        </h5>
                        <p class="text-xs text-slate-400 mt-1">
                            {{ app()->getLocale() === 'ar' ? 'تم إدراج 3 فرص جديدة في لوحة التحكم اليوم.' : '3 new leads have been funneled to the pipeline today.' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Dashboard Preferences / Configuration Quick Link -->
            <div class="pt-4 border-t border-slate-200 dark:border-slate-800">
                <button class="w-full btn-secondary text-xs">
                    {{ app()->getLocale() === 'ar' ? 'تخصيص لوحة المؤشرات' : 'Customize Dashboard' }}
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
