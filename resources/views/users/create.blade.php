<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('users.index') }}" class="p-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg transition-colors cursor-pointer">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                    {{ app()->getLocale() === 'ar' ? 'إضافة حساب مستخدم جديد' : 'Create User Account' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ app()->getLocale() === 'ar' ? 'قم بإنشاء هوية رقمية وتعيين الأدوار الوظيفية والصلاحيات للموظف.' : 'Register new digital credentials and select active roles for employee identity profiles.' }}
                </p>
            </div>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Panel: General Account Fields -->
            <div class="lg:col-span-2 space-y-6">
                <div class="erp-card p-6 space-y-6">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'بيانات الهوية الرقمية' : 'Digital Access Profile' }}</h3>
                        <p class="text-xs text-slate-400 mt-1">{{ app()->getLocale() === 'ar' ? 'تُستخدم هذه المعلومات لتسجيل الدخول إلى البوابة والأنظمة الفرعية.' : 'Core login credentials utilized for ERP system authentication.' }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form-input name="name" :label="app()->getLocale() === 'ar' ? 'الاسم بالكامل' : 'Full Name'" placeholder="e.g. Abdullah Ahmad" required />
                        <x-form-input name="email" type="email" :label="app()->getLocale() === 'ar' ? 'البريد الإلكتروني المهني' : 'Work Email Address'" placeholder="e.g. name@company.com" required />
                        
                        <x-form-input name="password" type="password" :label="app()->getLocale() === 'ar' ? 'كلمة المرور' : 'System Password'" required />
                        <x-form-input name="password_confirmation" type="password" :label="app()->getLocale() === 'ar' ? 'تأكيد كلمة المرور' : 'Confirm Password'" required />
                    </div>
                </div>
            </div>

            <!-- Right Panel: Role Assignment Checklist -->
            <div class="space-y-6">
                <div class="erp-card p-6 space-y-6">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'إسناد الأدوار الوظيفية' : 'Assign Job Roles' }}</h3>
                        <p class="text-xs text-slate-400 mt-1">{{ app()->getLocale() === 'ar' ? 'اختر دوراً واحداً أو أكثر لتحديد مجموعة الصلاحيات الممنوحة.' : 'Grant custom accessibility levels by assigning roles.' }}</p>
                    </div>

                    <div class="space-y-3 max-h-[280px] overflow-y-auto pr-1">
                        @forelse($roles as $role)
                            <div class="flex items-start gap-2.5 p-2 hover:bg-slate-50 dark:hover:bg-slate-800/40 rounded-lg transition-colors">
                                <input 
                                    type="checkbox" 
                                    name="roles[]" 
                                    id="role_{{ $role->id }}" 
                                    value="{{ $role->id }}"
                                    {{ is_array(old('roles')) && in_array($role->id, old('roles')) ? 'checked' : '' }}
                                    class="rounded border-slate-300 text-brand-600 focus:ring-brand-500 w-4 h-4 cursor-pointer mt-0.5"
                                >
                                <label for="role_{{ $role->id }}" class="text-xs font-bold text-slate-700 dark:text-slate-300 leading-normal select-none cursor-pointer">
                                    {{ $role->name }}
                                    @if($role->description)
                                        <span class="block text-[10px] text-slate-400 font-normal mt-0.5">{{ $role->description }}</span>
                                    @endif
                                </label>
                            </div>
                        @empty
                            <div class="text-center py-6 text-slate-400 text-xs">
                                {{ app()->getLocale() === 'ar' ? 'يرجى إنشاء الأدوار أولاً في لوحة الأدوار.' : 'Define job roles in RBAC settings first.' }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Footer Form Actions -->
        <div class="sticky bottom-0 bg-white/80 dark:bg-slate-900/80 backdrop-blur border-t border-slate-200/80 dark:border-slate-800/80 -mx-4 md:-mx-8 px-6 py-4 flex items-center justify-end gap-3 z-10">
            <a href="{{ route('users.index') }}" class="btn-secondary text-sm font-bold">
                {{ __('common.actions.cancel') }}
            </a>
            <button type="submit" class="btn-primary shadow-md shadow-brand-500/10 hover:shadow-brand-500/20 active:scale-98 transition-transform font-bold text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ app()->getLocale() === 'ar' ? 'حفظ المستخدم الجديد' : 'Save Account' }}</span>
            </button>
        </div>
    </form>
</x-app-layout>
