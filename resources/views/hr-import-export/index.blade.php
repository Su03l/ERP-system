<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                    {{ app()->getLocale() === 'ar' ? 'الاستيراد والتصدير' : 'Import & Export' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ app()->getLocale() === 'ar' ? 'استيراد بيانات الموظفين والحضور، وتصدير التقارير المجمعة.' : 'Import employees and attendance data, export aggregated reports.' }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Employee Import Card -->
        <div class="erp-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-start gap-4">
                <div class="w-12 h-12 rounded-lg bg-brand-50 dark:bg-brand-900/30 text-brand-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'استيراد الموظفين' : 'Import Employees' }}</h3>
                    <p class="text-sm text-slate-500 mt-1">{{ app()->getLocale() === 'ar' ? 'قم برفع ملف CSV أو Excel يحتوي على بيانات الموظفين لإضافتهم دفعة واحدة.' : 'Upload a CSV or Excel file containing employee data to add them in bulk.' }}</p>
                </div>
            </div>
            <div class="p-6 bg-slate-50/50 dark:bg-slate-800/20 flex-1">
                <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-xl p-8 text-center bg-white dark:bg-slate-900 mb-4 transition-colors hover:border-brand-500 hover:bg-brand-50/50 dark:hover:bg-brand-900/10">
                    <svg class="w-10 h-10 mx-auto text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ app()->getLocale() === 'ar' ? 'اسحب وأفلت الملف هنا' : 'Drag and drop file here' }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ app()->getLocale() === 'ar' ? 'أو انقر لاختيار ملف من جهازك' : 'Or click to select a file' }}</p>
                </div>
                <div class="flex items-center justify-between">
                    <button class="text-sm font-medium text-brand-600 hover:text-brand-700 underline" disabled>
                        {{ app()->getLocale() === 'ar' ? 'تحميل قالب الاستيراد' : 'Download Import Template' }}
                    </button>
                    <button class="btn-primary px-4 py-2 opacity-50 cursor-not-allowed" disabled>
                        {{ app()->getLocale() === 'ar' ? 'بدء الاستيراد' : 'Start Import' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Attendance Import Card -->
        <div class="erp-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-start gap-4">
                <div class="w-12 h-12 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'استيراد سجلات الحضور' : 'Import Attendance Records' }}</h3>
                    <p class="text-sm text-slate-500 mt-1">{{ app()->getLocale() === 'ar' ? 'رفع بيانات البصمة وسجلات الدخول والخروج من الأجهزة الخارجية.' : 'Upload fingerprint data and clock-in/out records from external devices.' }}</p>
                </div>
            </div>
            <div class="p-6 bg-slate-50/50 dark:bg-slate-800/20 flex-1">
                <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-xl p-8 text-center bg-white dark:bg-slate-900 mb-4 transition-colors hover:border-blue-500 hover:bg-blue-50/50 dark:hover:bg-blue-900/10">
                    <svg class="w-10 h-10 mx-auto text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ app()->getLocale() === 'ar' ? 'اسحب وأفلت الملف هنا' : 'Drag and drop file here' }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ app()->getLocale() === 'ar' ? 'أو انقر لاختيار ملف من جهازك' : 'Or click to select a file' }}</p>
                </div>
                <div class="flex items-center justify-between">
                    <button class="text-sm font-medium text-blue-600 hover:text-blue-700 underline" disabled>
                        {{ app()->getLocale() === 'ar' ? 'تحميل قالب الحضور' : 'Download Attendance Template' }}
                    </button>
                    <button class="btn-secondary px-4 py-2 opacity-50 cursor-not-allowed" disabled>
                        {{ app()->getLocale() === 'ar' ? 'بدء الاستيراد' : 'Start Import' }}
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
