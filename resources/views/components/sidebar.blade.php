<div 
    x-show="mobileSidebarOpen" 
    class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-30 md:hidden" 
    x-cloak 
    @click="mobileSidebarOpen = false"
></div>

<aside 
    :class="mobileSidebarOpen ? 'flex fixed inset-y-0 {{ app()->getLocale() === 'ar' ? 'right-0' : 'left-0' }} z-40' : 'hidden md:flex'"
    class="flex-col w-64 shrink-0 bg-slate-900 border-e border-slate-800 text-slate-300 h-full select-none transition-all duration-300" 
    id="sidebar-container"
>
    <!-- Header Branding / Logo -->
    <div class="h-16 flex items-center px-6 border-b border-slate-800 gap-3 justify-between">
        <div class="flex items-center gap-3">
            <!-- Abstract Brand Icon (Emerald Gradient Seed/Core) -->
            <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-brand-600 to-emerald-400 flex items-center justify-center shadow-lg shadow-brand-500/20">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"></path>
                </svg>
            </div>
            <div class="flex flex-col">
                <span class="font-bold text-white text-base tracking-tight leading-none">
                    {{ app()->getLocale() === 'ar' ? 'نواة ERP' : 'Nawwat ERP' }}
                </span>
                <span class="text-[10px] text-slate-500 font-semibold tracking-wider uppercase mt-1">
                    {{ app()->getLocale() === 'ar' ? 'نظام السحابي المتكامل' : 'SaaS Enterprise Core' }}
                </span>
            </div>
        </div>
        <!-- Mobile Sidebar Close Trigger -->
        <button @click="mobileSidebarOpen = false" class="md:hidden p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition-colors focus:outline-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <!-- Navigation List (Scrollable Scrollbar hidden) -->
    <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-7" style="scrollbar-width: none;">
        <!-- Group 1: General Workspace -->
        <div>
            <span class="px-2 text-[10px] font-bold text-slate-500 tracking-wider uppercase">
                {{ app()->getLocale() === 'ar' ? 'مساحة العمل' : 'Workspace' }}
            </span>
            <div class="mt-2 space-y-1">
                <a href="{{ url('/dashboard') }}" class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->is('dashboard') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/10' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->is('dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-white' }} {{ app()->getLocale() === 'ar' ? 'ml-3' : 'mr-3' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'لوحة التحكم الرئيسية' : 'Dashboard' }}</span>
                </a>
            </div>
        </div>

        <!-- Group 2: HRMS & Operations -->
        <div>
            <span class="px-2 text-[10px] font-bold text-slate-500 tracking-wider uppercase">
                {{ app()->getLocale() === 'ar' ? 'الموارد البشرية والعمليات' : 'HRMS & Operations' }}
            </span>
            <div class="mt-2 space-y-1">
                <!-- Employees -->
                <a href="{{ url('/employees') }}" class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->is('employees*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/10' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->is('employees*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }} {{ app()->getLocale() === 'ar' ? 'ml-3' : 'mr-3' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'إدارة الموظفين' : 'Employee Directory' }}</span>
                </a>

                <!-- Attendance -->
                <a href="{{ url('/attendance-records') }}" class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->is('attendance*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/10' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->is('attendance*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }} {{ app()->getLocale() === 'ar' ? 'ml-3' : 'mr-3' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'الحضور والانصراف' : 'Attendance Log' }}</span>
                </a>

                <!-- Leave Requests -->
                <a href="{{ url('/leave-requests') }}" class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->is('leave-requests*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/10' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->is('leave-requests*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }} {{ app()->getLocale() === 'ar' ? 'ml-3' : 'mr-3' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'طلبات الإجازات' : 'Leave Requests' }}</span>
                </a>

                <!-- Payroll -->
                <a href="{{ url('/payroll-runs') }}" class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->is('payroll*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/10' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->is('payroll*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }} {{ app()->getLocale() === 'ar' ? 'ml-3' : 'mr-3' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'مسيرات الرواتب' : 'Payroll Runs' }}</span>
                </a>
            </div>
        </div>

        <!-- Group 3: Financials & Accounting -->
        <div>
            <span class="px-2 text-[10px] font-bold text-slate-500 tracking-wider uppercase">
                {{ app()->getLocale() === 'ar' ? 'المالية والمحاسبة' : 'Finance & Accounting' }}
            </span>
            <div class="mt-2 space-y-1">
                <!-- Accounts -->
                <a href="{{ url('/accounts') }}" class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->is('accounts*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/10' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->is('accounts*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }} {{ app()->getLocale() === 'ar' ? 'ml-3' : 'mr-3' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'دليل الحسابات' : 'Chart of Accounts' }}</span>
                </a>

                <!-- Journal Entries -->
                <a href="{{ url('/journal-entries') }}" class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->is('journal-entries*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/10' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->is('journal-entries*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }} {{ app()->getLocale() === 'ar' ? 'ml-3' : 'mr-3' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'القيود اليومية' : 'Journal Entries' }}</span>
                </a>
            </div>
        </div>

        <!-- Group 4: Projects & Core CRM -->
        <div>
            <span class="px-2 text-[10px] font-bold text-slate-500 tracking-wider uppercase">
                {{ app()->getLocale() === 'ar' ? 'المشاريع والعملاء' : 'Projects & CRM' }}
            </span>
            <div class="mt-2 space-y-1">
                <!-- Projects -->
                <a href="{{ url('/projects') }}" class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->is('projects*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/10' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->is('projects*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }} {{ app()->getLocale() === 'ar' ? 'ml-3' : 'mr-3' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'إدارة المشاريع' : 'Projects Core' }}</span>
                </a>

                <!-- CRM Contacts -->
                <a href="{{ url('/crm-contacts') }}" class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->is('crm-contacts*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/10' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->is('crm-contacts*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }} {{ app()->getLocale() === 'ar' ? 'ml-3' : 'mr-3' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'العملاء والجهات' : 'CRM Contacts' }}</span>
                </a>
            </div>
        </div>

        <!-- Group 5: Company Administration -->
        <div>
            <span class="px-2 text-[10px] font-bold text-slate-500 tracking-wider uppercase">
                {{ app()->getLocale() === 'ar' ? 'إدارة النظام' : 'System Administration' }}
            </span>
            <div class="mt-2 space-y-1">
                <!-- Security Settings -->
                <a href="{{ url('/security-settings') }}" class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->is('security-settings*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/10' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->is('security-settings*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }} {{ app()->getLocale() === 'ar' ? 'ml-3' : 'mr-3' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'الحماية والأمان' : 'Security Suite' }}</span>
                </a>

                <!-- Audit Logs -->
                <a href="{{ url('/audit-logs') }}" class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->is('audit-logs*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/10' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->is('audit-logs*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }} {{ app()->getLocale() === 'ar' ? 'ml-3' : 'mr-3' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'سجلات التدقيق' : 'Audit Trails' }}</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Sidebar Tenant Footer -->
    <div class="p-4 border-t border-slate-800 bg-slate-950/40">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-slate-800 flex items-center justify-center font-bold text-slate-300">
                {{ substr(auth()->user()?->name ?? 'U', 0, 2) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-white truncate leading-none">
                    {{ auth()->user()?->name ?? 'Guest User' }}
                </p>
                <p class="text-[10px] text-slate-500 font-medium truncate mt-1">
                    {{ auth()->user()?->company?->name ?? 'Default Company' }}
                </p>
            </div>
        </div>
    </div>
</aside>
