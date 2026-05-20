<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                    {{ app()->getLocale() === 'ar' ? 'المسميات الوظيفية' : 'Job Titles' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ app()->getLocale() === 'ar' ? 'إدارة المسميات الوظيفية والمستويات.' : 'Manage job titles and levels.' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                @can('create', \App\Models\JobTitle::class)
                    <a href="{{ route('job-titles.create') }}" class="btn-primary px-4 py-2 text-sm font-semibold">
                        <svg class="w-4 h-4 shrink-0 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>{{ app()->getLocale() === 'ar' ? 'إضافة مسمى وظيفي' : 'Add Job Title' }}</span>
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <!-- Data Table -->
    <div class="erp-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-4 font-semibold">{{ app()->getLocale() === 'ar' ? 'المسمى الوظيفي' : 'Job Title' }}</th>
                        <th class="px-6 py-4 font-semibold">{{ app()->getLocale() === 'ar' ? 'الوصف' : 'Description' }}</th>
                        <th class="px-6 py-4 font-semibold">{{ app()->getLocale() === 'ar' ? 'المستوى' : 'Level' }}</th>
                        <th class="px-6 py-4 font-semibold text-right">{{ app()->getLocale() === 'ar' ? 'إجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($jobTitles as $jobTitle)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900 dark:text-white">
                                    {{ app()->getLocale() === 'ar' ? $jobTitle->name_ar : $jobTitle->name_en }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-500">
                                {{ app()->getLocale() === 'ar' ? ($jobTitle->description_ar ?? '-') : ($jobTitle->description_en ?? '-') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                                    {{ $jobTitle->level ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @can('update', $jobTitle)
                                        <a href="{{ route('job-titles.edit', $jobTitle) }}" class="p-2 text-slate-400 hover:text-blue-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                {{ app()->getLocale() === 'ar' ? 'لا توجد مسميات وظيفية مسجلة.' : 'No job titles found.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $jobTitles->links() }}
        </div>
    </div>
</x-app-layout>
