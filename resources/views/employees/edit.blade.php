<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('employees.index') }}" class="text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors">
                <svg class="w-6 h-6 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                    {{ app()->getLocale() === 'ar' ? 'تعديل بيانات الموظف' : 'Edit Employee' }}
                </h1>
                <p class="text-sm text-slate-500 mt-1">{{ app()->getLocale() === 'ar' ? ($employee->first_name_ar . ' ' . $employee->last_name_ar) : ($employee->first_name_en . ' ' . $employee->last_name_en) }}</p>
            </div>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('employees.update', $employee) }}" class="space-y-6">
        @csrf
        @method('PUT')
        
        <!-- Personal Info -->
        <div class="erp-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'المعلومات الشخصية' : 'Personal Information' }}</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        {{ app()->getLocale() === 'ar' ? 'الاسم الأول (عربي)' : 'First Name (Ar)' }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="first_name_ar" value="{{ old('first_name_ar', $employee->first_name_ar) }}" class="erp-input w-full" required>
                    @error('first_name_ar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        {{ app()->getLocale() === 'ar' ? 'الاسم الأخير (عربي)' : 'Last Name (Ar)' }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="last_name_ar" value="{{ old('last_name_ar', $employee->last_name_ar) }}" class="erp-input w-full" required>
                    @error('last_name_ar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        {{ app()->getLocale() === 'ar' ? 'الاسم الأول (إنجليزي)' : 'First Name (En)' }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="first_name_en" value="{{ old('first_name_en', $employee->first_name_en) }}" class="erp-input w-full" required>
                    @error('first_name_en') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        {{ app()->getLocale() === 'ar' ? 'الاسم الأخير (إنجليزي)' : 'Last Name (En)' }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="last_name_en" value="{{ old('last_name_en', $employee->last_name_en) }}" class="erp-input w-full" required>
                    @error('last_name_en') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        {{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }} <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email', $employee->email) }}" class="erp-input w-full" required>
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        {{ app()->getLocale() === 'ar' ? 'رقم الهاتف' : 'Phone' }}
                    </label>
                    <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" class="erp-input w-full">
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Employment Info -->
        <div class="erp-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'المعلومات الوظيفية' : 'Employment Information' }}</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        {{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}
                    </label>
                    <select name="department_id" class="erp-input w-full">
                        <option value="">-- {{ app()->getLocale() === 'ar' ? 'اختر القسم' : 'Select Department' }} --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? $dept->name_ar : $dept->name_en }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        {{ app()->getLocale() === 'ar' ? 'المسمى الوظيفي' : 'Job Title' }}
                    </label>
                    <select name="job_title_id" class="erp-input w-full">
                        <option value="">-- {{ app()->getLocale() === 'ar' ? 'اختر المسمى' : 'Select Job Title' }} --</option>
                        @foreach($jobTitles as $job)
                            <option value="{{ $job->id }}" {{ old('job_title_id', $employee->job_title_id) == $job->id ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? $job->name_ar : $job->name_en }}
                            </option>
                        @endforeach
                    </select>
                    @error('job_title_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        {{ app()->getLocale() === 'ar' ? 'المدير المباشر' : 'Direct Manager' }}
                    </label>
                    <select name="manager_id" class="erp-input w-full">
                        <option value="">-- {{ app()->getLocale() === 'ar' ? 'اختر المدير' : 'Select Manager' }} --</option>
                        @foreach($managers as $mgr)
                            <option value="{{ $mgr->id }}" {{ old('manager_id', $employee->manager_id) == $mgr->id ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? ($mgr->first_name_ar . ' ' . $mgr->last_name_ar) : ($mgr->first_name_en . ' ' . $mgr->last_name_en) }}
                            </option>
                        @endforeach
                    </select>
                    @error('manager_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        {{ app()->getLocale() === 'ar' ? 'تاريخ التعيين' : 'Hire Date' }}
                    </label>
                    <input type="date" name="hire_date" value="{{ old('hire_date', optional($employee->hire_date)->toDateString()) }}" class="erp-input w-full">
                    @error('hire_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        @can('employees.salary-visibility')
        <div class="erp-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'معلومات الراتب' : 'Salary Information' }}</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        {{ app()->getLocale() === 'ar' ? 'الراتب الأساسي' : 'Base Salary' }}
                    </label>
                    <input type="number" step="0.01" name="base_salary" value="{{ old('base_salary', $employee->base_salary) }}" class="erp-input w-full">
                    @error('base_salary') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
        @endcan

        <div class="flex items-center justify-end gap-4 sticky bottom-6 bg-white dark:bg-slate-900 p-4 border border-slate-200 dark:border-slate-800 shadow-xl rounded-xl">
            <a href="{{ route('employees.index') }}" class="btn-secondary px-6 py-2.5">
                {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
            </a>
            <button type="submit" class="btn-primary px-8 py-2.5 shadow-md hover:shadow-lg transition-shadow">
                {{ app()->getLocale() === 'ar' ? 'تحديث الموظف' : 'Update Employee' }}
            </button>
        </div>
    </form>
</x-app-layout>
