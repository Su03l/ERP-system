<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                {{ app()->getLocale() === 'ar' ? 'الملف الشخصي والحساب' : 'My Profile & Account' }}
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                {{ app()->getLocale() === 'ar' ? 'إدارة معلومات الحساب، تفضيلات اللغة، الجلسات النشطة ورموز الاتصال الخاصة بك.' : 'Manage account identity, security passwords, active sessions, and API integrations.' }}
            </p>
        </div>
    </x-slot>

    <!-- Profile Dashboard Tab Layout -->
    <div x-data="{ activeTab: 'info' }" class="space-y-6">
        <!-- Tabs Navigation -->
        <div class="flex border-b border-slate-200 dark:border-slate-800 overflow-x-auto select-none no-scrollbar">
            <button 
                @click="activeTab = 'info'"
                :class="activeTab === 'info' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 font-medium'"
                class="whitespace-nowrap py-4 px-6 border-b-2 text-sm transition-colors cursor-pointer"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'البيانات الشخصية' : 'Personal Info' }}</span>
                </div>
            </button>
            <button 
                @click="activeTab = 'password'"
                :class="activeTab === 'password' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 font-medium'"
                class="whitespace-nowrap py-4 px-6 border-b-2 text-sm transition-colors cursor-pointer"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'تغيير كلمة المرور' : 'Change Password' }}</span>
                </div>
            </button>
            <button 
                @click="activeTab = 'sessions'"
                :class="activeTab === 'sessions' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 font-medium'"
                class="whitespace-nowrap py-4 px-6 border-b-2 text-sm transition-colors cursor-pointer"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'الأجهزة والجلسات' : 'Active Sessions' }}</span>
                </div>
            </button>
            <button 
                @click="activeTab = 'tokens'"
                :class="activeTab === 'tokens' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 font-medium'"
                class="whitespace-nowrap py-4 px-6 border-b-2 text-sm transition-colors cursor-pointer"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m-2 4a2 2 0 012 2m-2 4a2 2 0 012 2M5 7a2 2 0 012-2m-2 4a2 2 0 012-2m-2 4a2 2 0 012-2M9 9h.01M9 12h.01M9 15h.01M12 9h.01M12 12h.01M12 15h.01"></path></svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'رموز الاتصال API' : 'Personal Tokens' }}</span>
                </div>
            </button>
            <button 
                @click="activeTab = 'notifications'"
                :class="activeTab === 'notifications' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 font-medium'"
                class="whitespace-nowrap py-4 px-6 border-b-2 text-sm transition-colors cursor-pointer"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'تفضيلات الإشعارات' : 'Notification Toggles' }}</span>
                </div>
            </button>
        </div>

        <!-- 1. Profile Info Form Content -->
        <div x-show="activeTab === 'info'" class="erp-card p-6 space-y-6" x-transition>
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'المعلومات الشخصية واللغة المفضلة' : 'Identity & Locale preferences' }}</h3>
                <p class="text-xs text-slate-400 mt-1">{{ app()->getLocale() === 'ar' ? 'قم بتحديث الاسم والبريد الإلكتروني واختيار لغة عرض لوحات التحكم.' : 'Keep user contact details updated and specify localized visual languages.' }}</p>
            </div>
            <form method="POST" action="{{ route('profile.update') }}" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @csrf
                <x-form-input name="name" :label="app()->getLocale() === 'ar' ? 'اسم المستخدم' : 'Full Name'" :value="$user->name" required />
                <x-form-input name="email" type="email" :label="app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email Address'" :value="$user->email" required />
                
                <x-form-select 
                    name="preferred_locale" 
                    :label="app()->getLocale() === 'ar' ? 'اللغة المفضلة' : 'Preferred Language'" 
                    :options="['ar' => __('common.locales.ar'), 'en' => __('common.locales.en')]" 
                    :selected="$user->preferred_locale" 
                    required 
                />

                <div class="md:col-span-2 flex justify-end">
                    <button type="submit" class="btn-primary font-bold text-sm shadow-md shadow-brand-500/10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        <span>{{ __('common.actions.save') }}</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- 2. Security / Password Update Tab Content -->
        <div x-show="activeTab === 'password'" class="erp-card p-6 space-y-6" x-transition>
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'تحديث كلمة المرور' : 'Reset Account Password' }}</h3>
                <p class="text-xs text-slate-400 mt-1">{{ app()->getLocale() === 'ar' ? 'تأكد من اختيار كلمة مرور قوية وغير مكررة لحماية الحساب.' : 'Ensure that you use a complex combination of symbols, numbers and characters.' }}</p>
            </div>
            <form method="POST" action="{{ route('profile.password') }}" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @csrf
                <x-form-input name="current_password" type="password" :label="app()->getLocale() === 'ar' ? 'كلمة المرور الحالية' : 'Current Password'" required />
                <x-form-input name="password" type="password" :label="app()->getLocale() === 'ar' ? 'كلمة المرور الجديدة' : 'New Password'" required />
                <x-form-input name="password_confirmation" type="password" :label="app()->getLocale() === 'ar' ? 'تأكيد كلمة المرور الجديدة' : 'Confirm New Password'" required />

                <div class="md:col-span-3 flex justify-end">
                    <button type="submit" class="btn-primary font-bold text-sm shadow-md shadow-brand-500/10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        <span>{{ app()->getLocale() === 'ar' ? 'تغيير الكلمة' : 'Change Password' }}</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- 3. Active Sessions Tab Content -->
        <div x-show="activeTab === 'sessions'" class="erp-card p-6 space-y-6" x-transition>
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'الأجهزة والجلسات النشطة حالياً' : 'Active Connected Browser Sessions' }}</h3>
                <p class="text-xs text-slate-400 mt-1">{{ app()->getLocale() === 'ar' ? 'سجل بجميع المتصفحات وعناوين IP التي قامت بتسجيل الدخول لحسابك مؤخراً.' : 'Revoke session access from other locations or review active login entries.' }}</p>
            </div>
            
            <div class="erp-table-container">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>{{ app()->getLocale() === 'ar' ? 'عنوان IP' : 'IP Address' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'آخر نشاط' : 'Last Activity' }}</th>
                            <th class="text-center">{{ app()->getLocale() === 'ar' ? 'خيارات' : 'Options' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                            <tr>
                                <td class="font-mono text-xs">{{ $session['ip_address'] }}</td>
                                <td class="text-xs">{{ $session['last_activity_at'] }}</td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('profile.sessions.revoke', $session['id']) }}" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من إنهاء هذه الجلسة؟' : 'Are you sure you want to terminate this session?' }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-rose-500 hover:text-rose-700 font-bold">
                                            {{ app()->getLocale() === 'ar' ? 'إنهاء الجلسة' : 'Revoke Session' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-6 text-slate-400">
                                    {{ app()->getLocale() === 'ar' ? 'لا توجد جلسات أخرى نشطة حالياً.' : 'No other active sessions detected.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. API Token Generator Tab Content -->
        <div x-show="activeTab === 'tokens'" class="space-y-6" x-transition>
            <!-- Plain Text Token Alert Presentation -->
            @if(session('plain_text_token'))
                <div class="p-5 bg-teal-50 dark:bg-teal-950/20 border border-teal-200 dark:border-teal-900 rounded-xl space-y-3">
                    <div class="flex items-center gap-2 text-teal-800 dark:text-teal-400 font-bold text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>{{ app()->getLocale() === 'ar' ? 'احفظ هذا الرمز فوراً!' : 'Save this Token immediately!' }}</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-normal">
                        {{ app()->getLocale() === 'ar' ? 'لأسباب أمنية، لن تتمكن من رؤية هذا الرمز مرة أخرى بمجرد مغادرة الصفحة.' : 'For security reasons, this token will only be shown once.' }}
                    </p>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly value="{{ session('plain_text_token') }}" class="erp-input flex-1 font-mono text-xs bg-slate-50 dark:bg-slate-900 select-all border-teal-300">
                        <button onclick="navigator.clipboard.writeText('{{ session('plain_text_token') }}'); alert('{{ app()->getLocale() === 'ar' ? 'تم النسخ!' : 'Copied!' }}')" class="btn-primary text-xs py-2 px-4 shrink-0 font-bold bg-teal-600 hover:bg-teal-700">
                            {{ app()->getLocale() === 'ar' ? 'نسخ' : 'Copy' }}
                        </button>
                    </div>
                </div>
            @endif

            <!-- Token Generator Form -->
            <div class="erp-card p-6 space-y-6">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'إنشاء رمز اتصال شخصي جديد (API Token)' : 'Generate Personal API Access Token' }}</h3>
                    <p class="text-xs text-slate-400 mt-1">{{ app()->getLocale() === 'ar' ? 'تتيح لك الرموز ربط حسابك بتطبيقات خارجية بأمان تام وبصلاحيات محددة.' : 'Create direct access keys with scoped abilities to hook external services.' }}</p>
                </div>
                <form method="POST" action="{{ route('profile.tokens.create') }}" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form-input name="name" :label="app()->getLocale() === 'ar' ? 'اسم الرمز / الغرض' : 'Token Label'" placeholder="e.g. ERP Integration" required />
                        <x-form-input name="expires_at" type="date" :label="app()->getLocale() === 'ar' ? 'تاريخ الانتهاء (اختياري)' : 'Expiration date (Optional)'" />
                    </div>

                    <!-- Scopes / Abilities Checklist -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 tracking-wide uppercase">
                            {{ app()->getLocale() === 'ar' ? 'صلاحيات وقدرات الرمز المحددة' : 'Token Abilities Scopes' }}
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 max-h-[180px] overflow-y-auto border border-slate-100 dark:border-slate-800 p-3 rounded-lg">
                            @foreach($permissions as $perm)
                                <x-form-checkbox 
                                    name="abilities[]" 
                                    :id="'ability_'.$perm->id" 
                                    :value="$perm->key" 
                                    :label="$perm->name" 
                                />
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary font-bold text-sm shadow-md shadow-brand-500/10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>{{ app()->getLocale() === 'ar' ? 'إنشاء رمز' : 'Generate Token' }}</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- List of existing Active Tokens -->
            <div class="erp-card p-6 space-y-4">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wide">
                    {{ app()->getLocale() === 'ar' ? 'رموز الاتصال النشطة حالياً' : 'Active Integrations Tokens' }}
                </h4>
                <div class="erp-table-container">
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th>{{ app()->getLocale() === 'ar' ? 'اسم الرمز' : 'Token Name' }}</th>
                                <th>{{ app()->getLocale() === 'ar' ? 'تاريخ الانتهاء' : 'Expires At' }}</th>
                                <th>{{ app()->getLocale() === 'ar' ? 'الصلاحيات' : 'Abilities' }}</th>
                                <th class="text-center">{{ app()->getLocale() === 'ar' ? 'إجراءات' : 'Actions' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tokens as $token)
                                <tr>
                                    <td class="font-bold text-xs">{{ $token->name }}</td>
                                    <td class="text-xs font-mono text-slate-400">{{ $token->expires_at ? $token->expires_at->toDateString() : __('N/A') }}</td>
                                    <td class="text-xs font-mono text-slate-400 max-w-[200px] truncate" title="{{ implode(', ', $token->abilities ?? []) }}">
                                        {{ empty($token->abilities) ? '*' : implode(', ', $token->abilities) }}
                                    </td>
                                    <td class="text-center">
                                        <form method="POST" action="{{ route('profile.tokens.revoke', $token->id) }}" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من إلغاء هذا الرمز؟' : 'Are you sure you want to revoke this token?' }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-rose-500 hover:text-rose-700 font-bold">
                                                {{ app()->getLocale() === 'ar' ? 'إلغاء الصلاحية' : 'Revoke Token' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-6 text-slate-400">
                                        {{ app()->getLocale() === 'ar' ? 'لا توجد رموز اتصال نشطة لحسابك حالياً.' : 'No active tokens found.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 5. Notifications Preferences Placeholder -->
        <div x-show="activeTab === 'notifications'" class="erp-card p-6 space-y-6" x-transition>
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'قنوات استلام التنبيهات والرسائل' : 'Notification Channel Settings' }}</h3>
                <p class="text-xs text-slate-400 mt-1">{{ app()->getLocale() === 'ar' ? 'خصص الأحداث التي ترغب باستلام رسائل بريدية أو فورية عنها داخل النظام.' : 'Specify how you would like to be notified for requests, approvals and workflows.' }}</p>
            </div>
            
            <div class="space-y-4">
                <x-form-toggle 
                    name="profile_notify_email" 
                    :label="app()->getLocale() === 'ar' ? 'استلام تنبيهات طلبات الإجازات بالبريد' : 'Receive Email notifications for leave requests'" 
                    :checked="true" 
                />
                
                <x-form-toggle 
                    name="profile_notify_web" 
                    :label="app()->getLocale() === 'ar' ? 'استلام إشعارات فورية داخل لوحة التحكم' : 'Show system alerts in live topbar notifications dropdown'" 
                    :checked="true" 
                />

                <x-form-toggle 
                    name="profile_notify_weekly" 
                    :label="app()->getLocale() === 'ar' ? 'إرسال ملخص أسبوعي تشغيلي للأداء' : 'Receive weekly analytical summary performance briefs'" 
                    :checked="false" 
                />
            </div>
        </div>
    </div>
</x-app-layout>
