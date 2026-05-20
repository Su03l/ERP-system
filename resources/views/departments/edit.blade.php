<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('departments.index') }}" class="text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
                <svg class="w-6 h-6 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                    {{ app()->getLocale() === 'ar' ? 'تعديل القسم' : 'Edit Department' }}
                </h1>
                <p class="text-sm text-slate-500 mt-1">{{ app()->getLocale() === 'ar' ? $department->name_ar : $department->name_en }}</p>
            </div>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('departments.update', $department) }}" class="space-y-6 max-w-4xl mx-auto">
        @csrf
        @method('PUT')
        
        <div class="erp-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'بيانات القسم' : 'Department Details' }}</h3>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'اسم القسم (عربي)' : 'Department Name (Ar)' }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name_ar" value="{{ old('name_ar', $department->name_ar) }}" class="erp-input w-full" required>
                        @error('name_ar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'اسم القسم (إنجليزي)' : 'Department Name (En)' }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name_en" value="{{ old('name_en', $department->name_en) }}" class="erp-input w-full" required>
                        @error('name_en') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'القسم الرئيسي' : 'Parent Department' }}
                        </label>
                        <select name="parent_id" class="erp-input w-full">
                            <option value="">-- {{ app()->getLocale() === 'ar' ? 'بدون قسم رئيسي' : 'No Parent' }} --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('parent_id', $department->parent_id) == $dept->id ? 'selected' : '' }}>
                                    {{ app()->getLocale() === 'ar' ? $dept->name_ar : $dept->name_en }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'مدير القسم' : 'Department Manager' }}
                        </label>
                        <select name="manager_id" class="erp-input w-full">
                            <option value="">-- {{ app()->getLocale() === 'ar' ? 'اختر المدير' : 'Select Manager' }} --</option>
                            @foreach($managers as $mgr)
                                <option value="{{ $mgr->id }}" {{ old('manager_id', $department->manager_id) == $mgr->id ? 'selected' : '' }}>
                                    {{ app()->getLocale() === 'ar' ? ($mgr->first_name_ar . ' ' . $mgr->last_name_ar) : ($mgr->first_name_en . ' ' . $mgr->last_name_en) }}
                                </option>
                            @endforeach
                        </select>
                        @error('manager_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-600" {{ old('is_active', $department->is_active) ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ app()->getLocale() === 'ar' ? 'القسم نشط' : 'Department is active' }}</span>
                    </label>
                    @error('is_active') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('departments.index') }}" class="btn-secondary px-6 py-2.5">
                {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
            </a>
            <button type="submit" class="btn-primary px-8 py-2.5 shadow-md hover:shadow-lg transition-shadow">
                {{ app()->getLocale() === 'ar' ? 'تحديث القسم' : 'Update Department' }}
            </button>
        </div>
    </form>
</x-app-layout>
