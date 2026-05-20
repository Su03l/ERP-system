<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                    {{ app()->getLocale() === 'ar' ? 'هيكلة الأقسام' : 'Departments Structure' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ app()->getLocale() === 'ar' ? 'إدارة الأقسام والوحدات الإدارية وتعيين المدراء.' : 'Manage administrative units and department managers.' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                @can('create', \App\Models\Department::class)
                    <a href="{{ route('departments.create') }}" class="btn-primary px-4 py-2 text-sm font-semibold">
                        <svg class="w-4 h-4 shrink-0 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>{{ app()->getLocale() === 'ar' ? 'إضافة قسم' : 'Add Department' }}</span>
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <!-- Data Table / Tree -->
    <div class="erp-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-4 font-semibold">{{ app()->getLocale() === 'ar' ? 'اسم القسم' : 'Department Name' }}</th>
                        <th class="px-6 py-4 font-semibold">{{ app()->getLocale() === 'ar' ? 'القسم الرئيسي' : 'Parent Dept' }}</th>
                        <th class="px-6 py-4 font-semibold">{{ app()->getLocale() === 'ar' ? 'المدير' : 'Manager' }}</th>
                        <th class="px-6 py-4 font-semibold">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                        <th class="px-6 py-4 font-semibold text-right">{{ app()->getLocale() === 'ar' ? 'إجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($departments as $department)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900 dark:text-white">
                                    {{ app()->getLocale() === 'ar' ? $department->name_ar : $department->name_en }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-500">
                                {{ app()->getLocale() === 'ar' ? ($department->parent->name_ar ?? '-') : ($department->parent->name_en ?? '-') }}
                            </td>
                            <td class="px-6 py-4">
                                @if($department->manager)
                                    <a href="{{ route('employees.show', $department->manager) }}" class="font-medium text-slate-900 dark:text-white hover:text-brand-600">
                                        {{ app()->getLocale() === 'ar' ? ($department->manager->first_name_ar . ' ' . $department->manager->last_name_ar) : ($department->manager->first_name_en . ' ' . $department->manager->last_name_en) }}
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($department->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                        {{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300">
                                        {{ app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @can('update', $department)
                                        <a href="{{ route('departments.edit', $department) }}" class="p-2 text-slate-400 hover:text-blue-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                {{ app()->getLocale() === 'ar' ? 'لا توجد أقسام مسجلة.' : 'No departments found.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $departments->links() }}
        </div>
    </div>
</x-app-layout>
