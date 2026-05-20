<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                    {{ app()->getLocale() === 'ar' ? 'سجلات الحضور' : 'Attendance Records' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ app()->getLocale() === 'ar' ? 'استعراض وإدارة سجلات الدخول والخروج للموظفين.' : 'View and manage clock-in/out records for employees.' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('hr-import-export.index') }}" class="btn-secondary px-4 py-2 text-sm font-semibold">
                    <svg class="w-4 h-4 shrink-0 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'استيراد السجلات' : 'Import Records' }}</span>
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Filters Section -->
    <div class="erp-card p-5 mb-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl">
        <form method="GET" action="{{ route('attendance-records.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'بحث باسم الموظف أو الرقم' : 'Search by Employee Name or ID' }}
                </label>
                <input type="text" name="search" value="{{ request('search') }}" class="erp-input w-full" placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل اسم الموظف...' : 'Enter employee name...' }}">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'من تاريخ' : 'From Date' }}
                </label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="erp-input w-full">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'إلى تاريخ' : 'To Date' }}
                </label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="erp-input w-full">
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
                        <th class="px-6 py-4 font-semibold">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</th>
                        <th class="px-6 py-4 font-semibold">{{ app()->getLocale() === 'ar' ? 'وقت الدخول' : 'Clock In' }}</th>
                        <th class="px-6 py-4 font-semibold">{{ app()->getLocale() === 'ar' ? 'وقت الخروج' : 'Clock Out' }}</th>
                        <th class="px-6 py-4 font-semibold">{{ app()->getLocale() === 'ar' ? 'ساعات العمل' : 'Work Hrs' }}</th>
                        <th class="px-6 py-4 font-semibold text-right">{{ app()->getLocale() === 'ar' ? 'إجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($records as $record)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900/30 text-brand-600 flex items-center justify-center font-bold text-xs">
                                        {{ mb_substr($record->employee->first_name_ar ?? $record->employee->first_name_en, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-slate-900 dark:text-white">
                                            {{ app()->getLocale() === 'ar' ? ($record->employee->first_name_ar . ' ' . $record->employee->last_name_ar) : ($record->employee->first_name_en . ' ' . $record->employee->last_name_en) }}
                                        </div>
                                        <p class="text-xs text-slate-500">{{ $record->employee->employee_code }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-medium">
                                {{ \Carbon\Carbon::parse($record->attendance_date)->format('Y-m-d') }}
                            </td>
                            <td class="px-6 py-4 text-emerald-600 dark:text-emerald-400 font-bold">
                                {{ $record->clock_in_at ? \Carbon\Carbon::parse($record->clock_in_at)->format('H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-amber-600 dark:text-amber-400 font-bold">
                                {{ $record->clock_out_at ? \Carbon\Carbon::parse($record->clock_out_at)->format('H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <!-- Temporary placeholder since work_hours might not be pre-calculated natively -->
                                @if($record->clock_in_at && $record->clock_out_at)
                                    {{ gmdate('H:i', \Carbon\Carbon::parse($record->clock_in_at)->diffInSeconds(\Carbon\Carbon::parse($record->clock_out_at))) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button class="text-slate-400 hover:text-brand-600 p-2" title="{{ app()->getLocale() === 'ar' ? 'عرض التفاصيل' : 'View Details' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                {{ app()->getLocale() === 'ar' ? 'لا توجد سجلات حضور مطابقة للبحث.' : 'No attendance records found.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $records->links() }}
        </div>
    </div>
</x-app-layout>
