<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('employees.index') }}" class="text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
                <svg class="w-6 h-6 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                    {{ app()->getLocale() === 'ar' ? 'ملف الموظف' : 'Employee Profile' }}
                </h1>
            </div>
            <div class="mr-auto">
                @can('update', $employee)
                    <a href="{{ route('employees.edit', $employee) }}" class="btn-primary px-4 py-2 text-sm">
                        {{ app()->getLocale() === 'ar' ? 'تعديل البيانات' : 'Edit Profile' }}
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Sidebar: Main Info -->
        <div class="lg:col-span-1 space-y-6">
            <div class="erp-card p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm text-center">
                <div class="w-24 h-24 mx-auto rounded-full bg-brand-100 dark:bg-brand-900/30 text-brand-600 flex items-center justify-center text-4xl font-bold mb-4">
                    {{ mb_substr($employee->first_name_ar ?? $employee->first_name_en, 0, 1) }}
                </div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-1">
                    {{ app()->getLocale() === 'ar' ? ($employee->first_name_ar . ' ' . $employee->last_name_ar) : ($employee->first_name_en . ' ' . $employee->last_name_en) }}
                </h2>
                <p class="text-slate-500 mb-4">{{ $employee->jobTitle->name_ar ?? $employee->jobTitle->name_en ?? '-' }}</p>

                @if($employee->status === 'active')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                        {{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300">
                        {{ app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive' }}
                    </span>
                @endif
            </div>

            <!-- Contact Info -->
            <div class="erp-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 font-bold text-slate-900 dark:text-white">
                    {{ app()->getLocale() === 'ar' ? 'معلومات الاتصال' : 'Contact Information' }}
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <p class="text-xs font-medium text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</p>
                        <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $employee->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'رقم الهاتف' : 'Phone' }}</p>
                        <p class="text-sm font-medium text-slate-900 dark:text-white" dir="ltr">{{ $employee->phone ?? '-' }}</p>
                    </div>
                </div>
            </div>
            
            @can('employees.salary-visibility')
            <!-- Salary Info -->
            <div class="erp-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 font-bold text-slate-900 dark:text-white flex items-center justify-between">
                    <span>{{ app()->getLocale() === 'ar' ? 'الراتب والمستحقات' : 'Salary & Compensation' }}</span>
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <p class="text-xs font-medium text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'الراتب الأساسي' : 'Base Salary' }}</p>
                        <p class="text-xl font-bold text-slate-900 dark:text-white">{{ $employee->base_salary ? number_format($employee->base_salary, 2) : '-' }}</p>
                    </div>
                </div>
            </div>
            @endcan
        </div>

        <!-- Right Content: Tabs -->
        <div class="lg:col-span-2 space-y-6">
            <div class="erp-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
                <!-- Employment Info -->
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 font-bold text-lg text-slate-900 dark:text-white">
                    {{ app()->getLocale() === 'ar' ? 'البيانات الوظيفية' : 'Employment Details' }}
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs font-medium text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}</p>
                        <p class="text-sm font-medium text-slate-900 dark:text-white">
                            {{ app()->getLocale() === 'ar' ? ($employee->department->name_ar ?? '-') : ($employee->department->name_en ?? '-') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'المسمى الوظيفي' : 'Job Title' }}</p>
                        <p class="text-sm font-medium text-slate-900 dark:text-white">
                            {{ app()->getLocale() === 'ar' ? ($employee->jobTitle->name_ar ?? '-') : ($employee->jobTitle->name_en ?? '-') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'المدير المباشر' : 'Direct Manager' }}</p>
                        <p class="text-sm font-medium text-slate-900 dark:text-white">
                            @if($employee->manager)
                                <a href="{{ route('employees.show', $employee->manager) }}" class="text-brand-600 hover:underline">
                                    {{ app()->getLocale() === 'ar' ? ($employee->manager->first_name_ar . ' ' . $employee->manager->last_name_ar) : ($employee->manager->first_name_en . ' ' . $employee->manager->last_name_en) }}
                                </a>
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'تاريخ التعيين' : 'Hire Date' }}</p>
                        <p class="text-sm font-medium text-slate-900 dark:text-white">
                            {{ optional($employee->hire_date)->format('Y-m-d') ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Placeholders for Documents, Leave, Attendance -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Leave Placeholder -->
                <div class="erp-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 mx-auto flex items-center justify-center text-slate-400 mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <h3 class="font-bold text-slate-900 dark:text-white mb-1">{{ app()->getLocale() === 'ar' ? 'سجل الإجازات' : 'Leave Records' }}</h3>
                    <p class="text-xs text-slate-500 mb-3">{{ app()->getLocale() === 'ar' ? 'قريباً' : 'Coming soon' }}</p>
                    <button class="btn-secondary text-xs px-3 py-1" disabled>{{ app()->getLocale() === 'ar' ? 'عرض السجل' : 'View Records' }}</button>
                </div>

                <!-- Documents Placeholder -->
                <div class="erp-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-6 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 mx-auto flex items-center justify-center text-slate-400 mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="font-bold text-slate-900 dark:text-white mb-1">{{ app()->getLocale() === 'ar' ? 'المستندات' : 'Documents' }}</h3>
                    <p class="text-xs text-slate-500 mb-3">{{ app()->getLocale() === 'ar' ? 'قريباً' : 'Coming soon' }}</p>
                    <button class="btn-secondary text-xs px-3 py-1" disabled>{{ app()->getLocale() === 'ar' ? 'إدارة المستندات' : 'Manage Docs' }}</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
