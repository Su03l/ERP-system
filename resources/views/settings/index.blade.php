<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                {{ app()->getLocale() === 'ar' ? 'إعدادات الشركة والمؤسسة' : 'Company Settings & Preferences' }}
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                {{ app()->getLocale() === 'ar' ? 'إدارة الهوية البصرية، التفضيلات المحلية، سياسات الأمان والميزات التشغيلية.' : 'Configure company branding, localization settings, security policies, and feature suites.' }}
            </p>
        </div>
    </x-slot>

    <!-- Settings Dashboard Tab Layout -->
    <div x-data="{ activeTab: 'profile' }" class="space-y-6">
        <!-- Tabs Navigation -->
        <div class="flex border-b border-slate-200 dark:border-slate-800 overflow-x-auto select-none no-scrollbar">
            <button 
                @click="activeTab = 'profile'"
                :class="activeTab === 'profile' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 font-medium'"
                class="whitespace-nowrap py-4 px-6 border-b-2 text-sm transition-colors cursor-pointer"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'الملف التعريفي' : 'Company Profile' }}</span>
                </div>
            </button>
            <button 
                @click="activeTab = 'localization'"
                :class="activeTab === 'localization' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 font-medium'"
                class="whitespace-nowrap py-4 px-6 border-b-2 text-sm transition-colors cursor-pointer"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h2m-4-3h9M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'الإعدادات المحلية' : 'Localization' }}</span>
                </div>
            </button>
            <button 
                @click="activeTab = 'branding'"
                :class="activeTab === 'branding' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 font-medium'"
                class="whitespace-nowrap py-4 px-6 border-b-2 text-sm transition-colors cursor-pointer"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'الهوية البصرية' : 'Branding' }}</span>
                </div>
            </button>
            <button 
                @click="activeTab = 'security'"
                :class="activeTab === 'security' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 font-medium'"
                class="whitespace-nowrap py-4 px-6 border-b-2 text-sm transition-colors cursor-pointer"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'سياسات الأمان' : 'Security Policies' }}</span>
                </div>
            </button>
            <button 
                @click="activeTab = 'features'"
                :class="activeTab === 'features' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-bold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 font-medium'"
                class="whitespace-nowrap py-4 px-6 border-b-2 text-sm transition-colors cursor-pointer"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'الميزات والخيارات' : 'Feature Toggles' }}</span>
                </div>
            </button>
        </div>

        <!-- 1. Primary Settings Form (Profile, Localization, Branding, Notifications) -->
        <form method="POST" action="{{ route('company-settings.update') }}" class="space-y-6">
            @csrf

            <!-- Profile Tab Content -->
            <div x-show="activeTab === 'profile'" class="erp-card p-6 space-y-6" x-transition>
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'المعلومات الأساسية للمؤسسة' : 'General Company Details' }}</h3>
                    <p class="text-xs text-slate-400 mt-1">{{ app()->getLocale() === 'ar' ? 'أدخل البيانات القانونية والتجارية للشركة ليتم تطبيقها عبر الفواتير والمستندات.' : 'Provide official registration names and communication details used system-wide.' }}</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form-input name="name" :label="__('company.fields.name')" :value="$company->name" required />
                    <x-form-input name="legal_name" :label="__('company.fields.legal_name')" :value="$company->legal_name" />
                    <x-form-input name="email" type="email" :label="__('company.fields.email')" :value="$company->email" />
                    <x-form-input name="phone" :label="__('company.fields.phone')" :value="$company->phone" />
                </div>
            </div>

            <!-- Localization Tab Content -->
            <div x-show="activeTab === 'localization'" class="erp-card p-6 space-y-6" x-transition>
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'المنطقة الزمنية والعملة وأيام العمل' : 'Regional & Localization Preferences' }}</h3>
                    <p class="text-xs text-slate-400 mt-1">{{ app()->getLocale() === 'ar' ? 'اضبط معايير الوقت والتاريخ والعملات المناسبة لموقع نشاطك التجاري.' : 'Set standard times, formatting preferences, currencies and active working calendars.' }}</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form-select 
                        name="locale" 
                        :label="__('company.fields.locale')" 
                        :options="['ar' => __('common.locales.ar'), 'en' => __('common.locales.en')]" 
                        :selected="$company->locale" 
                        required 
                    />
                    
                    <x-form-select 
                        name="timezone" 
                        :label="__('company.fields.timezone')" 
                        :options="['Asia/Riyadh' => 'Asia/Riyadh (GMT+3)', 'Africa/Cairo' => 'Africa/Cairo (GMT+2)', 'Asia/Dubai' => 'Asia/Dubai (GMT+4)', 'UTC' => 'UTC (GMT+0)']" 
                        :selected="$company->timezone" 
                        required 
                    />

                    <x-form-select 
                        name="currency" 
                        :label="__('company.fields.currency')" 
                        :options="['SAR' => 'SAR - الريال السعودي', 'AED' => 'AED - الدرهم الإماراتي', 'EGP' => 'EGP - الجنيه المصري', 'USD' => 'USD - الدولار الأمريكي']" 
                        :selected="$company->currency" 
                        required 
                    />

                    <x-form-select 
                        name="date_preference" 
                        :label="__('company.fields.date_preference')" 
                        :options="['gregorian' => 'ميلادي / Gregorian', 'hijri' => 'هجري / Hijri']" 
                        :selected="$settings['settings']['date_preference'] ?? 'gregorian'" 
                        required 
                    />
                </div>

                <div class="space-y-3">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 tracking-wide uppercase">
                        {{ __('company.fields.working_days') }}
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-4">
                        @foreach(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
                            @php
                                $isChecked = in_array($day, $settings['settings']['working_days'] ?? ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday']);
                            @endphp
                            <x-form-checkbox 
                                name="working_days[]" 
                                :id="'day_'.$day"
                                :value="$day" 
                                :label="__('company.working_days.'.$day)" 
                                :checked="$isChecked" 
                            />
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Branding Tab Content -->
            <div x-show="activeTab === 'branding'" class="erp-card p-6 space-y-6" x-transition>
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">{{ __('company.fields.branding') }}</h3>
                    <p class="text-xs text-slate-400 mt-1">{{ app()->getLocale() === 'ar' ? 'خصص الهوية البصرية من شعار ودرجات ألوان للواجهة والتقارير المطبوعة.' : 'Set corporate visual designs, primary layout brand hexes and logo assets.' }}</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <x-form-input 
                        name="branding[logo_path]" 
                        :label="__('company.branding.logo_path')" 
                        :value="$settings['settings']['branding']['logo_path'] ?? ''" 
                        :help-text="app()->getLocale() === 'ar' ? 'رابط شعار الشركة المعتمد.' : 'Standard URL path for corporate identity logos.'" 
                    />
                    
                    <x-form-input 
                        name="branding[primary_color]" 
                        type="color" 
                        :label="__('company.branding.primary_color')" 
                        :value="$settings['settings']['branding']['primary_color'] ?? '#0d9488'" 
                    />

                    <x-form-input 
                        name="branding[secondary_color]" 
                        type="color" 
                        :label="__('company.branding.secondary_color')" 
                        :value="$settings['settings']['branding']['secondary_color'] ?? '#1e293b'" 
                    />
                </div>
            </div>

            <!-- Feature Toggles Placeholder -->
            <div x-show="activeTab === 'features'" class="erp-card p-6 space-y-6" x-transition>
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'تفضيلات الإشعارات والميزات' : 'Operational Features & Notification Preferences' }}</h3>
                    <p class="text-xs text-slate-400 mt-1">{{ app()->getLocale() === 'ar' ? 'تحكم في الميزات النشطة للمؤسسة وتفضيلات إرسال التنبيهات.' : 'Configure global SaaS features and email/database channel notification routing.' }}</p>
                </div>
                <div class="space-y-4">
                    <x-form-toggle 
                        name="notification_preferences[email_enabled]" 
                        :label="__('company.notifications.email_enabled')" 
                        :checked="(bool) ($settings['settings']['notification_preferences']['email_enabled'] ?? true)" 
                    />
                    
                    <x-form-toggle 
                        name="notification_preferences[database_enabled]" 
                        :label="__('company.notifications.database_enabled')" 
                        :checked="(bool) ($settings['settings']['notification_preferences']['database_enabled'] ?? true)" 
                    />

                    <x-form-toggle 
                        name="notification_preferences[sms_enabled]" 
                        :label="__('company.notifications.sms_enabled')" 
                        :checked="(bool) ($settings['settings']['notification_preferences']['sms_enabled'] ?? false)" 
                    />
                </div>
            </div>

            <!-- Sticky Save Actions Area for Primary Settings -->
            <div x-show="activeTab !== 'security'" class="sticky bottom-0 bg-white/80 dark:bg-slate-900/80 backdrop-blur border-t border-slate-200/80 dark:border-slate-800/80 -mx-4 md:-mx-8 px-6 py-4 flex items-center justify-end gap-3 z-10">
                <button type="submit" class="btn-primary shadow-md shadow-brand-500/10 hover:shadow-brand-500/20 active:scale-98 transition-transform font-bold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    <span>{{ __('common.actions.save') }}</span>
                </button>
            </div>
        </form>

        <!-- 2. Independent Security Form (Submits to security policy endpoint) -->
        <form method="POST" action="{{ route('company-settings.security') }}" class="space-y-6">
            @csrf

            <!-- Security Tab Content -->
            <div x-show="activeTab === 'security'" class="erp-card p-6 space-y-6" x-transition>
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'سياسات الحماية والأمان والتحكم بالدخول' : 'Access Control & Corporate Security Policies' }}</h3>
                    <p class="text-xs text-slate-400 mt-1">{{ app()->getLocale() === 'ar' ? 'قم بضبط معايير الأمان المتقدمة وتأمين جلسات المستخدمين وحماية البيانات الحساسة.' : 'Enforce multi-factor access, set session timeouts, IP allowlists and secure export filters.' }}</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form-input 
                        name="session_timeout_minutes" 
                        type="number" 
                        :label="app()->getLocale() === 'ar' ? 'مدة الجلسة (بالدقائق)' : 'Session Timeout (Minutes)'" 
                        :value="$securitySetting->session_timeout_minutes ?? 120" 
                        required 
                    />
                    
                    <x-form-input 
                        name="audit_retention_days" 
                        type="number" 
                        :label="app()->getLocale() === 'ar' ? 'مدة الاحتفاظ بسجلات التدقيق (بالأيام)' : 'Audit Retention Period (Days)'" 
                        :value="$securitySetting->audit_retention_days ?? 365" 
                        required 
                    />
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800/80 pt-6 space-y-4">
                    <x-form-toggle 
                        name="two_factor_authentication_enabled" 
                        :label="app()->getLocale() === 'ar' ? 'تفعيل المصادقة الثنائية الإلزامية (2FA)' : 'Mandatory Two-Factor Authentication (2FA)'" 
                        :checked="(bool) ($securitySetting->two_factor_authentication_enabled ?? false)" 
                        :help-text="app()->getLocale() === 'ar' ? 'إلزام جميع الموظفين بتفعيل المصادقة الثنائية لحماية حساباتهم.' : 'Force all company users to utilize 2FA upon authentication.'" 
                    />

                    <x-form-toggle 
                        name="export_approval_required" 
                        :label="app()->getLocale() === 'ar' ? 'طلب اعتماد عند تصدير البيانات الحساسة' : 'Require Approvals for Sensitive Data Exports'" 
                        :checked="(bool) ($securitySetting->export_approval_required ?? true)" 
                        :help-text="app()->getLocale() === 'ar' ? 'يتطلب تصدير ملفات الموظفين أو الرواتب موافقة رسمية من خلال سير العمل.' : 'Trigger workflows for leaves, payrolls, or documents exports.'" 
                    />
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800/80 pt-6 space-y-3">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 tracking-wide uppercase">
                        {{ app()->getLocale() === 'ar' ? 'عناوين IP المسموح لها بالدخول' : 'IP Address Allowlist' }}
                    </label>
                    @php
                        $ipsString = is_array($securitySetting->allowed_login_ips) ? implode(', ', $securitySetting->allowed_login_ips) : '';
                    @endphp
                    <x-form-input 
                        name="allowed_login_ips_raw" 
                        :value="$ipsString" 
                        :placeholder="app()->getLocale() === 'ar' ? 'أدخل العناوين مفصولة بفاصلة، مثل: 192.168.1.1, 10.0.0.1' : 'Separate values with commas, e.g., 192.168.1.1, 10.0.0.1'" 
                        :help-text="app()->getLocale() === 'ar' ? 'اتركه فارغاً للسماح بالدخول من جميع الشبكات.' : 'Leave blank to allow authentications from any location.'" 
                    />
                </div>
            </div>

            <!-- Sticky Save Actions Area for Security Policies -->
            <div x-show="activeTab === 'security'" class="sticky bottom-0 bg-white/80 dark:bg-slate-900/80 backdrop-blur border-t border-slate-200/80 dark:border-slate-800/80 -mx-4 md:-mx-8 px-6 py-4 flex items-center justify-end gap-3 z-10">
                <button type="submit" class="btn-primary shadow-md shadow-brand-500/10 hover:shadow-brand-500/20 active:scale-98 transition-transform font-bold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'تحديث السياسات' : 'Enforce Policies' }}</span>
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
