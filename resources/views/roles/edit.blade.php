<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('roles.index') }}" class="p-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg transition-colors cursor-pointer">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                    {{ app()->getLocale() === 'ar' ? 'تعديل صلاحيات الدور الوظيفي' : 'Edit Job Role & Permissions' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ app()->getLocale() === 'ar' ? 'تعديل البيانات وتحديث مصفوفة الصلاحيات الممنوحة لدور: ' : 'Modify details and adapt active accessibility permissions for: ' }} <strong>{{ $role->name }}</strong>
                </p>
            </div>
        </div>
    </x-slot>

    @php
        $groupedPermissions = $permissions->groupBy(function ($perm) {
            $parts = explode('.', $perm->key);
            return count($parts) > 1 ? $parts[0] : 'general';
        });
    @endphp

    <form method="POST" action="{{ route('roles.update', $role->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. Role Metadata Form -->
        <div class="erp-card p-6 space-y-6">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'المعلومات التعريفية للدور' : 'Role Identification details' }}</h3>
                <p class="text-xs text-slate-400 mt-1">{{ app()->getLocale() === 'ar' ? 'قم بتحديث الاسم والوصف. احذر عند تعديل الرمز التعريفي إذا كان مستخدماً في الكود.' : 'Name custom profiles clearly to facilitate assignment on team members.' }}</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form-input name="name" :label="app()->getLocale() === 'ar' ? 'اسم الدور' : 'Role Name'" :value="$role->name" placeholder="e.g. HR Manager" required />
                <x-form-input name="key" :label="app()->getLocale() === 'ar' ? 'رمز المعرف الفريد' : 'Key Identifier'" :value="$role->key" placeholder="e.g. hr_manager" required />
                <div class="md:col-span-2">
                    <x-form-input name="description" :label="app()->getLocale() === 'ar' ? 'الوصف الوظيفي للمسؤوليات' : 'Role Description & Scope'" :value="$role->description" placeholder="Describe the access permissions and business responsibilities associated with this role." />
                </div>
            </div>
        </div>

        <!-- 2. Interactive Permissions Matrix Grid -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wide">
                    {{ app()->getLocale() === 'ar' ? 'صلاحيات الدخول التفصيلية للوحدات' : 'Granular Module Access Matrix' }}
                </h3>
                
                <!-- Quick Filter / Selection Toggles -->
                <div class="flex items-center gap-3">
                    <button type="button" onclick="toggleAllCheckboxes(true)" class="text-xs text-brand-600 hover:text-brand-700 dark:text-brand-400 font-bold cursor-pointer">
                        {{ app()->getLocale() === 'ar' ? 'تحديد الكل' : 'Select All' }}
                    </button>
                    <span class="text-slate-300">|</span>
                    <button type="button" onclick="toggleAllCheckboxes(false)" class="text-xs text-slate-500 hover:text-slate-700 dark:text-slate-400 font-bold cursor-pointer">
                        {{ app()->getLocale() === 'ar' ? 'إلغاء التحديد' : 'Deselect All' }}
                    </button>
                </div>
            </div>

            <!-- Permission Modules Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($groupedPermissions as $module => $perms)
                    @php
                        $modulePermKeys = $perms->pluck('key')->toArray();
                        $allChecked = count(array_intersect($modulePermKeys, $rolePermissions)) === count($modulePermKeys);
                    @endphp
                    <div class="erp-card p-5 space-y-4 border border-slate-100 dark:border-slate-800/80">
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                            <span class="text-xs font-black uppercase text-brand-600 dark:text-brand-400 tracking-wider">
                                {{ strtoupper(str_replace('_', ' ', $module)) }}
                            </span>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-medium text-slate-400 select-none">
                                <input type="checkbox" onchange="toggleModuleCheckboxes('{{ $module }}', this.checked)" {{ $allChecked ? 'checked' : '' }} class="rounded border-slate-300 text-brand-600 focus:ring-brand-500 w-3.5 h-3.5 cursor-pointer">
                                <span>{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</span>
                            </label>
                        </div>

                        <!-- Checkboxes List -->
                        <div class="grid grid-cols-1 gap-2.5 max-h-[220px] overflow-y-auto pr-1">
                            @foreach($perms as $perm)
                                <div class="flex items-start gap-2">
                                    <input 
                                        type="checkbox" 
                                        name="permissions[]" 
                                        id="perm_{{ $perm->id }}" 
                                        value="{{ $perm->key }}"
                                        data-module="{{ $module }}"
                                        {{ in_array($perm->key, $rolePermissions) ? 'checked' : '' }}
                                        class="rounded border-slate-300 text-brand-600 focus:ring-brand-500 w-4 h-4 cursor-pointer mt-0.5"
                                    >
                                    <label for="perm_{{ $perm->id }}" class="text-xs font-semibold text-slate-700 dark:text-slate-300 leading-normal select-none cursor-pointer">
                                        {{ $perm->name }}
                                        @if($perm->description)
                                            <span class="block text-[10px] text-slate-400 font-normal mt-0.5">{{ $perm->description }}</span>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Sticky Form Footer Actions -->
        <div class="sticky bottom-0 bg-white/80 dark:bg-slate-900/80 backdrop-blur border-t border-slate-200/80 dark:border-slate-800/80 -mx-4 md:-mx-8 px-6 py-4 flex items-center justify-end gap-3 z-10">
            <a href="{{ route('roles.index') }}" class="btn-secondary text-sm font-bold">
                {{ __('common.actions.cancel') }}
            </a>
            <button type="submit" class="btn-primary shadow-md shadow-brand-500/10 hover:shadow-brand-500/20 active:scale-98 transition-transform font-bold text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ app()->getLocale() === 'ar' ? 'تحديث الدور' : 'Save Changes' }}</span>
            </button>
        </div>
    </form>

    <script>
        function toggleModuleCheckboxes(moduleName, isChecked) {
            document.querySelectorAll('input[data-module="' + moduleName + '"]').forEach(function(el) {
                el.checked = isChecked;
            });
        }

        function toggleAllCheckboxes(isChecked) {
            document.querySelectorAll('input[name="permissions[]"]').forEach(function(el) {
                el.checked = isChecked;
            });
            // Update modular check-all states
            document.querySelectorAll('input[onchange^="toggleModuleCheckboxes"]').forEach(function(el) {
                el.checked = isChecked;
            });
        }
    </script>
</x-app-layout>
