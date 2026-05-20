<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                    {{ app()->getLocale() === 'ar' ? 'سجل الموظفين' : 'Employees Directory' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ app()->getLocale() === 'ar' ? 'إدارة الموظفين والبحث والتصفية حسب الأقسام والمناصب.' : 'Manage employees, search and filter by department and job title.' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                @can('create', \App\Models\Employee::class)
                    <a href="{{ route('employees.create') }}" class="btn-primary px-4 py-2 text-sm font-semibold">
                        <svg class="w-4 h-4 shrink-0 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>{{ app()->getLocale() === 'ar' ? 'إضافة موظف' : 'Add Employee' }}</span>
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <!-- Filters Section -->
    <div class="erp-card p-5 mb-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl">
        <form method="GET" action="{{ route('employees.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'بحث' : 'Search' }}
                </label>
                <input type="text" name="search" value="{{ request('search') }}" class="erp-input w-full" placeholder="{{ app()->getLocale() === 'ar' ? 'الاسم، البريد...' : 'Name, Email...' }}">
            </div>
            
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}
                </label>
                <select name="department_id" class="erp-input w-full">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' ? $dept->name_ar : $dept->name_en }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'المسمى الوظيفي' : 'Job Title' }}
                </label>
                <select name="job_title_id" class="erp-input w-full">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                    @foreach($jobTitles as $job)
                        <option value="{{ $job->id }}" {{ request('job_title_id') == $job->id ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' ? $job->name_ar : $job->name_en }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="btn-secondary w-full py-2.5">
                    {{ app()->getLocale() === 'ar' ? 'تصفية' : 'Filter' }}
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="erp-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-4 font-semibold">{{ app()->getLocale() === 'ar' ? 'الموظف' : 'Employee' }}</th>
                        <th class="px-6 py-4 font-semibold">{{ app()->getLocale() === 'ar' ? 'القسم / المسمى' : 'Dept / Title' }}</th>
                        <th class="px-6 py-4 font-semibold">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                        @can('employees.salary-visibility')
                            <th class="px-6 py-4 font-semibold">{{ app()->getLocale() === 'ar' ? 'الراتب الأساسي' : 'Base Salary' }}</th>
                        @endcan
                        <th class="px-6 py-4 font-semibold text-right">{{ app()->getLocale() === 'ar' ? 'إجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($employees as $employee)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-brand-100 dark:bg-brand-900/30 text-brand-600 flex items-center justify-center font-bold">
                                        {{ mb_substr($employee->first_name_ar ?? $employee->first_name_en, 0, 1) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('employees.show', $employee) }}" class="font-medium text-slate-900 dark:text-white hover:text-brand-600">
                                            {{ app()->getLocale() === 'ar' ? ($employee->first_name_ar . ' ' . $employee->last_name_ar) : ($employee->first_name_en . ' ' . $employee->last_name_en) }}
                                        </a>
                                        <p class="text-xs text-slate-500">{{ $employee->employee_code }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium">{{ app()->getLocale() === 'ar' ? ($employee->department->name_ar ?? '-') : ($employee->department->name_en ?? '-') }}</p>
                                <p class="text-xs text-slate-500">{{ app()->getLocale() === 'ar' ? ($employee->jobTitle->name_ar ?? '-') : ($employee->jobTitle->name_en ?? '-') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @if($employee->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                        {{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300">
                                        {{ app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive' }}
                                    </span>
                                @endif
                            </td>
                            @can('employees.salary-visibility')
                                <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">
                                    {{ $employee->base_salary ? number_format($employee->base_salary, 2) : '-' }}
                                </td>
                            @endcan
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @can('view', $employee)
                                        <a href="{{ route('employees.show', $employee) }}" class="p-2 text-slate-400 hover:text-brand-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </a>
                                    @endcan
                                    @can('update', $employee)
                                        <a href="{{ route('employees.edit', $employee) }}" class="p-2 text-slate-400 hover:text-blue-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                {{ app()->getLocale() === 'ar' ? 'لا يوجد موظفين.' : 'No employees found.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $employees->links() }}
        </div>
    </div>
</x-app-layout>
