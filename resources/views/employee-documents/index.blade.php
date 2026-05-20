<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                    {{ app()->getLocale() === 'ar' ? 'مستندات الموظفين' : 'Employee Documents' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ app()->getLocale() === 'ar' ? 'إدارة الوثائق، العقود، والجوازات للموظفين.' : 'Manage documents, contracts, and passports for employees.' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button class="btn-primary px-4 py-2 text-sm font-semibold opacity-50 cursor-not-allowed" disabled>
                    <svg class="w-4 h-4 shrink-0 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'رفع مستند' : 'Upload Document' }}</span>
                </button>
            </div>
        </div>
    </x-slot>

    <!-- Expiring Soon Notice -->
    <div class="erp-card p-4 mb-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl shadow-sm flex items-start gap-4">
        <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center text-amber-600 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <div>
            <h3 class="font-bold text-amber-900 dark:text-amber-300">{{ app()->getLocale() === 'ar' ? 'مستندات قاربت على الانتهاء' : 'Documents Expiring Soon' }}</h3>
            <p class="text-sm text-amber-700 dark:text-amber-400 mt-1">
                {{ app()->getLocale() === 'ar' ? 'لا يوجد مستندات تقترب من تاريخ الانتهاء حالياً.' : 'No documents are currently nearing expiration.' }}
            </p>
        </div>
    </div>

    <!-- Data Table -->
    <div class="erp-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-4 font-semibold">{{ app()->getLocale() === 'ar' ? 'المستند' : 'Document' }}</th>
                        <th class="px-6 py-4 font-semibold">{{ app()->getLocale() === 'ar' ? 'الموظف' : 'Employee' }}</th>
                        <th class="px-6 py-4 font-semibold">{{ app()->getLocale() === 'ar' ? 'تاريخ الانتهاء' : 'Expiry Date' }}</th>
                        <th class="px-6 py-4 font-semibold">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                        <th class="px-6 py-4 font-semibold text-right">{{ app()->getLocale() === 'ar' ? 'إجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($documents as $document)
                        <!-- Document Row Placeholder -->
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <p>{{ app()->getLocale() === 'ar' ? 'لا توجد مستندات مرفوعة.' : 'No documents uploaded yet.' }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
