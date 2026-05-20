<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('job-titles.index') }}" class="text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
                <svg class="w-6 h-6 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                    {{ app()->getLocale() === 'ar' ? 'إضافة مسمى وظيفي' : 'Add Job Title' }}
                </h1>
            </div>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('job-titles.store') }}" class="space-y-6 max-w-4xl mx-auto">
        @csrf
        
        <div class="erp-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'بيانات المسمى' : 'Job Title Details' }}</h3>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Ar)' }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name_ar" value="{{ old('name_ar') }}" class="erp-input w-full" required>
                        @error('name_ar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (En)' }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name_en" value="{{ old('name_en') }}" class="erp-input w-full" required>
                        @error('name_en') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'الوصف (عربي)' : 'Description (Ar)' }}
                        </label>
                        <textarea name="description_ar" class="erp-input w-full" rows="3">{{ old('description_ar') }}</textarea>
                        @error('description_ar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'الوصف (إنجليزي)' : 'Description (En)' }}
                        </label>
                        <textarea name="description_en" class="erp-input w-full" rows="3">{{ old('description_en') }}</textarea>
                        @error('description_en') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        {{ app()->getLocale() === 'ar' ? 'المستوى' : 'Level' }}
                    </label>
                    <input type="text" name="level" value="{{ old('level') }}" class="erp-input w-full md:w-1/2">
                    @error('level') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('job-titles.index') }}" class="btn-secondary px-6 py-2.5">
                {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
            </a>
            <button type="submit" class="btn-primary px-8 py-2.5 shadow-md hover:shadow-lg transition-shadow">
                {{ app()->getLocale() === 'ar' ? 'حفظ' : 'Save' }}
            </button>
        </div>
    </form>
</x-app-layout>
