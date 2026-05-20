<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('users.index') }}" class="p-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg transition-colors cursor-pointer">
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                        {{ app()->getLocale() === 'ar' ? 'تفاصيل حساب المستخدم' : 'User Account Details' }}
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        {{ app()->getLocale() === 'ar' ? 'عرض تفاصيل الهوية الرقمية، الأدوار، ومستويات الصلاحيات الممنوحة لـ: ' : 'Digital access profile, roles, and authorization structure for: ' }} <strong>{{ $user->name }}</strong>
                    </p>
                </div>
            </div>
            @if(auth()->user()->hasPermission('users.update', auth()->user()->company_id))
                <div class="shrink-0">
                    <a href="{{ route('users.edit', $user->id) }}" class="btn-primary shadow-md shadow-brand-500/10 active:scale-98 transition-transform font-bold text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        <span>{{ app()->getLocale() === 'ar' ? 'تعديل الحساب' : 'Edit Account' }}</span>
                    </a>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Right Column (Desktop) / Top Column (Mobile): User Core Identity & Employee File -->
        <div class="space-y-6 lg:col-span-1">
            <!-- User Info Card -->
            <div class="erp-card p-6 flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-full bg-brand-50 dark:bg-brand-950/20 text-brand-600 dark:text-brand-400 font-extrabold flex items-center justify-center text-2xl select-none shadow-inner border border-brand-100 dark:border-brand-900/50 mb-4">
                    {{ mb_strtoupper(mb_substr($user->name, 0, 2)) }}
                </div>
                <h2 class="text-lg font-bold text-slate-800 dark:text-white leading-snug">{{ $user->name }}</h2>
                <p class="text-xs text-slate-400 mt-1 font-mono break-all">{{ $user->email }}</p>

                @if($user->id === auth()->user()->id)
                    <span class="inline-block mt-3 text-[10px] bg-brand-100 text-brand-800 dark:bg-brand-950/40 dark:text-brand-400 border border-brand-200/50 dark:border-brand-900/50 px-2.5 py-0.5 rounded-full font-bold">
                        {{ app()->getLocale() === 'ar' ? 'حسابك الشخصي' : 'Your Personal Account' }}
                    </span>
                @endif

                <div class="w-full border-t border-slate-100 dark:border-slate-800/80 my-5"></div>

                <div class="w-full space-y-3.5 text-right rtl:text-right ltr:text-left">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">
                            {{ app()->getLocale() === 'ar' ? 'حالة الحساب' : 'Account Status' }}
                        </span>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-teal-50 dark:bg-teal-950/20 border border-teal-200/50 dark:border-teal-900/50 rounded-full text-teal-700 dark:text-teal-400 font-bold text-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-500 animate-pulse"></span>
                            <span>{{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}</span>
                        </div>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">
                            {{ app()->getLocale() === 'ar' ? 'اللغة المفضلة' : 'Preferred Language' }}
                        </span>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                            {{ $user->preferred_locale === 'ar' ? 'العربية (AR)' : 'English (EN)' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">
                            {{ app()->getLocale() === 'ar' ? 'عضو منذ' : 'Member Since' }}
                        </span>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 font-mono">
                            {{ $user->created_at ? $user->created_at->format('Y-m-d') : '—' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Employee File Link Card -->
            <div class="erp-card p-6 space-y-4">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'الملف المهني للموظف' : 'Linked Employee File' }}</h3>
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                </div>

                @if($user->employeeProfile)
                    <div class="space-y-3">
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 rounded-xl flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-400 block">{{ app()->getLocale() === 'ar' ? 'كود الموظف' : 'Employee ID' }}</span>
                                <span class="text-sm font-bold text-slate-800 dark:text-white font-mono">#{{ $user->employeeProfile->employee_code ?: $user->employeeProfile->id }}</span>
                            </div>
                            <a href="/employees/{{ $user->employeeProfile->id }}" class="p-2 bg-white hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-brand-600 dark:text-brand-400 rounded-lg text-xs font-bold transition-colors">
                                {{ app()->getLocale() === 'ar' ? 'عرض الملف' : 'View File' }}
                            </a>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-1">
                            <div>
                                <span class="text-[10px] text-slate-400 block mb-0.5">{{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}</span>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                    {{ $user->employeeProfile->department ? $user->employeeProfile->department->name : '—' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block mb-0.5">{{ app()->getLocale() === 'ar' ? 'المسمى الوظيفي' : 'Job Title' }}</span>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                    {{ $user->employeeProfile->jobTitle ? $user->employeeProfile->jobTitle->name : '—' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-6">
                        <div class="w-10 h-10 bg-slate-50 dark:bg-slate-800/30 rounded-full flex items-center justify-center mx-auto text-slate-400 mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <p class="text-xs text-slate-400 leading-normal">{{ app()->getLocale() === 'ar' ? 'هذا المستخدم غير مرتبط بملف موظف نشط حالياً.' : 'This access account is not currently linked to an employee file.' }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Left Column: Permissions Matrix & Audit Placeholder -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Roles Summary Card -->
            <div class="erp-card p-6 space-y-4">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'الأدوار المسندة' : 'Assigned ERP Roles' }}</h3>
                    <span class="px-2 py-0.5 bg-brand-50 dark:bg-brand-950/20 text-brand-700 dark:text-brand-400 text-[10px] font-bold rounded border border-brand-100 dark:border-brand-900/50">
                        {{ $user->roles->count() }} {{ app()->getLocale() === 'ar' ? 'أدوار نشطة' : 'Active Roles' }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($user->roles as $role)
                        <div class="p-3 border border-slate-100 dark:border-slate-800/80 rounded-xl bg-slate-50/50 dark:bg-slate-800/20 space-y-1">
                            <span class="text-xs font-bold text-slate-800 dark:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                                <span>{{ $role->name }}</span>
                            </span>
                            @if($role->description)
                                <p class="text-[10px] text-slate-400 font-normal leading-normal pl-3 rtl:pl-0 rtl:pr-3">{{ $role->description }}</p>
                            @endif
                        </div>
                    @empty
                        <div class="md:col-span-2 text-center py-6 text-slate-400 text-xs">
                            {{ app()->getLocale() === 'ar' ? 'لم يتم إسناد أي أدوار لهذا المستخدم بعد.' : 'No roles have been assigned to this user yet.' }}
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Inherited Permissions Grid -->
            <div class="erp-card p-6 space-y-4">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'صلاحيات الوصول النشطة' : 'Active Access Permissions' }}</h3>
                    <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] font-bold rounded border border-slate-200 dark:border-slate-700">
                        {{ $permissions->count() }} {{ app()->getLocale() === 'ar' ? 'صلاحية مخولة' : 'Authorized Permissions' }}
                    </span>
                </div>

                @if($permissions->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-[350px] overflow-y-auto pr-1">
                        @foreach($permissions as $perm)
                            <div class="flex items-center gap-2 p-2 bg-slate-50/40 dark:bg-slate-800/10 border border-slate-100/80 dark:border-slate-850 rounded-lg text-xs">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4"></path></svg>
                                <div>
                                    <span class="font-bold text-slate-700 dark:text-slate-300 block leading-none">{{ $perm->name }}</span>
                                    <span class="text-[9px] text-slate-400 font-mono leading-none mt-1 block">{{ $perm->key }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10 text-slate-400">
                        <div class="w-10 h-10 bg-slate-50 dark:bg-slate-800/30 rounded-full flex items-center justify-center mx-auto text-slate-350 mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <p class="text-xs text-slate-400 leading-normal">{{ app()->getLocale() === 'ar' ? 'هذا الحساب لا يملك صلاحيات وصول نشطة في الوقت الحالي.' : 'This account does not have any active privileges configured.' }}</p>
                    </div>
                @endif
            </div>

            <!-- User Activity Timeline Placeholder -->
            <div class="erp-card p-6 space-y-4">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'مؤشرات النشاط الأخير' : 'Recent Account Actions' }}</h3>
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>

                <div class="relative border-r border-slate-200 dark:border-slate-700/80 pr-4 space-y-6 py-2 rtl:border-r rtl:border-l-0 ltr:border-l ltr:border-r-0 ltr:pl-4 ltr:pr-0">
                    <div class="relative">
                        <span class="absolute right-[-21px] rtl:right-[-21px] ltr:left-[-21px] ltr:right-auto top-1 w-2.5 h-2.5 bg-brand-500 rounded-full ring-4 ring-white dark:ring-slate-900"></span>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5">
                            <span class="text-xs font-bold text-slate-800 dark:text-white">{{ app()->getLocale() === 'ar' ? 'تسجيل دخول ناجح للنظام' : 'Successful Authentication Session' }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ now()->format('Y-m-d H:i') }}</span>
                        </div>
                        <p class="text-[10px] text-slate-450 leading-normal mt-1">{{ app()->getLocale() === 'ar' ? 'تم الوصول بنجاح من عنوان IP مسموح به باستخدام متصفح ويب قياسي.' : 'Direct ERP portal login established securely via authorized IP.' }}</p>
                    </div>

                    <div class="relative">
                        <span class="absolute right-[-21px] rtl:right-[-21px] ltr:left-[-21px] ltr:right-auto top-1 w-2.5 h-2.5 bg-slate-300 dark:bg-slate-700 rounded-full ring-4 ring-white dark:ring-slate-900"></span>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-350">{{ app()->getLocale() === 'ar' ? 'قراءة لوحة تحكم التقارير المالية' : 'Analytics & Invoices Dashboard Viewed' }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ now()->subHours(2)->format('Y-m-d H:i') }}</span>
                        </div>
                        <p class="text-[10px] text-slate-450 leading-normal mt-1">{{ app()->getLocale() === 'ar' ? 'استعراض تحليلات المبيعات ونسب الإنجاز الشهرية.' : 'Loaded reports index and retrieved company performance KPI cards.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
