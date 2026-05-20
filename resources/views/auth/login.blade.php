<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full bg-slate-50 dark:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ app()->getLocale() === 'ar' ? 'تسجيل الدخول - نواة ERP' : 'Log In - Nawwat ERP' }}</title>

    @fonts

    <!-- Scripts and Stylesheets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full text-slate-800 dark:text-slate-200 antialiased overflow-hidden bg-slate-50 dark:bg-slate-950 font-sans">
    <div class="flex min-h-screen w-full">
        
        <!-- Left Side: Interactive Glassmorphic Brand Showcase (Hidden on Mobile) -->
        <div class="hidden lg:flex lg:w-1/2 relative bg-gradient-to-br from-slate-950 via-slate-900 to-brand-900 text-white p-12 flex-col justify-between overflow-hidden">
            <!-- Animated Radial Light Orb Background -->
            <div class="absolute top-0 right-0 w-[500px] h-[500px] rounded-full bg-brand-500/10 blur-[120px] -mr-40 -mt-40 pointer-events-none animate-pulse"></div>
            <div class="absolute bottom-0 left-0 w-[400px] h-[400px] rounded-full bg-emerald-500/10 blur-[100px] -ml-40 -mb-40 pointer-events-none animate-pulse" style="animation-duration: 6s;"></div>

            <!-- Top Header Brand logo -->
            <div class="flex items-center gap-3 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-emerald-400 flex items-center justify-center shadow-lg shadow-brand-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"></path>
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-white text-lg tracking-tight leading-none">
                        {{ app()->getLocale() === 'ar' ? 'نواة ERP' : 'Nawwat ERP' }}
                    </span>
                    <span class="text-[10px] text-brand-200 font-semibold tracking-wider uppercase mt-1">
                        {{ app()->getLocale() === 'ar' ? 'النظام السحابي المتكامل للمؤسسات' : 'SaaS Enterprise Core' }}
                    </span>
                </div>
            </div>

            <!-- Core Pitch/Statement Section -->
            <div class="my-auto max-w-lg space-y-6 relative z-10">
                <h2 class="text-4xl font-extrabold text-white leading-tight tracking-tight">
                    {{ app()->getLocale() === 'ar' ? 'أتمتة ذكية وموثوقة لعمليات شركتك المالية والتشغيلية.' : 'Reliable, intelligent automation for your corporate financials and operations.' }}
                </h2>
                <p class="text-slate-300 text-base leading-relaxed">
                    {{ app()->getLocale() === 'ar' ? 'منصة متكاملة تدعم تعدد المنشآت، إدارة الموارد البشرية والرواتب، الحسابات العامة والأصول، والمبيعات والعملاء، مصممة خصيصاً لتواكب نمو وتطور أعمالك.' : 'An integrated platform supporting multi-tenancy, HRMS & payroll, accounting & assets, and CRM, specifically tailored to accelerate the growth of your business.' }}
                </p>

                <!-- Micro-feature list / Glassmorphic status board -->
                <div class="grid grid-cols-2 gap-4 pt-6">
                    <div class="bg-white/5 border border-white/10 rounded-xl p-4 backdrop-blur-md flex items-center gap-3 hover:bg-white/10 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-brand-500/20 text-brand-300 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <span class="text-xs font-semibold text-slate-200">
                            {{ app()->getLocale() === 'ar' ? 'حماية فائقة للبيانات' : 'Highly Secured Isolation' }}
                        </span>
                    </div>
                    <div class="bg-white/5 border border-white/10 rounded-xl p-4 backdrop-blur-md flex items-center gap-3 hover:bg-white/10 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-brand-500/20 text-brand-300 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <span class="text-xs font-semibold text-slate-200">
                            {{ app()->getLocale() === 'ar' ? 'محرك ذكاء مالي سريع' : 'Ultra-Fast Performance' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Footer Meta Info -->
            <div class="flex items-center justify-between text-xs text-slate-500 relative z-10 border-t border-slate-800 pt-6">
                <span>© {{ date('Y') }} Nawwat ERP. {{ app()->getLocale() === 'ar' ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}</span>
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:text-slate-300 transition-colors">{{ app()->getLocale() === 'ar' ? 'الشروط والأحكام' : 'Terms' }}</a>
                    <a href="#" class="hover:text-slate-300 transition-colors">{{ app()->getLocale() === 'ar' ? 'سياسة الخصوصية' : 'Privacy' }}</a>
                </div>
            </div>
        </div>

        <!-- Right Side: Login Form Shell -->
        <div class="w-full lg:w-1/2 flex flex-col justify-between p-8 sm:p-16 md:p-24 bg-white dark:bg-slate-950 relative">
            <!-- Language and Mode Toggles in Topbar -->
            <div class="flex items-center justify-between">
                <div>
                    <!-- Mobile Logo Indicator -->
                    <div class="lg:hidden flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-brand-600 to-emerald-400 flex items-center justify-center shadow-md">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"></path>
                            </svg>
                        </div>
                        <span class="font-extrabold text-slate-900 dark:text-white text-base tracking-tight">{{ app()->getLocale() === 'ar' ? 'نواة' : 'Nawwat' }}</span>
                    </div>
                </div>

                <!-- Localization Selector -->
                <div class="flex items-center gap-2">
                    @if(app()->getLocale() === 'ar')
                        <a href="?locale=en" class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white px-3 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors">
                            <span>English</span>
                            <span class="text-[9px] bg-slate-100 dark:bg-slate-900 text-slate-500 px-1 py-0.5 rounded">EN</span>
                        </a>
                    @else
                        <a href="?locale=ar" class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white px-3 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors">
                            <span class="font-arabic">العربية</span>
                            <span class="text-[9px] bg-slate-100 dark:bg-slate-900 text-slate-500 px-1 py-0.5 rounded">AR</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Login Center Area Card -->
            <div class="my-auto max-w-md w-full mx-auto space-y-8">
                <!-- Branding Welcome Header -->
                <div class="space-y-3">
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-tight">
                        {{ app()->getLocale() === 'ar' ? 'مرحباً بك مجدداً!' : 'Welcome Back!' }}
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm">
                        {{ app()->getLocale() === 'ar' ? 'سجل دخولك الآن للوصول إلى لوحة تحكم المنشأة.' : 'Enter your details below to securely access your cloud workspace.' }}
                    </p>
                </div>

                <!-- Validation Error Displays (Laravel Standard Validation System) -->
                @if ($errors->any())
                    <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/50 flex items-start gap-3 text-rose-800 dark:text-rose-400 animate-pulse" role="alert">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <div class="text-xs space-y-1">
                            <span class="font-bold">{{ app()->getLocale() === 'ar' ? 'يرجى مراجعة وتصحيح الأخطاء التالية:' : 'Please resolve the following authentication issues:' }}</span>
                            <ul class="list-disc {{ app()->getLocale() === 'ar' ? 'pr-4' : 'pl-4' }} space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Flash notification banner -->
                @if (session('success'))
                    <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/50 flex items-center gap-2 text-xs font-semibold text-emerald-800 dark:text-emerald-400" role="alert">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <!-- Login Native Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-6" id="login-form">
                    @csrf

                    <!-- Email Input Block -->
                    <div class="space-y-2">
                        <label for="email" class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider block">
                            {{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني المهني' : 'Work Email Address' }}
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 {{ app()->getLocale() === 'ar' ? 'right-0 pr-3.5' : 'left-0 pl-3.5' }} flex items-center text-slate-400 dark:text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"></path></svg>
                            </span>
                            <input type="email" name="email" id="email" value="{{ old('email', 'test@example.com') }}" required autocomplete="username"
                                class="erp-input w-full dark:bg-slate-900 border-slate-200 dark:border-slate-800 {{ app()->getLocale() === 'ar' ? 'pr-11' : 'pl-11' }} text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500" 
                                placeholder="name@company.com">
                        </div>
                    </div>

                    <!-- Password Input Block -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="password" class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider block">
                                {{ app()->getLocale() === 'ar' ? 'كلمة المرور السرية' : 'Secret Password' }}
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs font-semibold text-brand-500 hover:text-brand-600 transition-colors">
                                    {{ app()->getLocale() === 'ar' ? 'نسيت كلمة المرور؟' : 'Forgot password?' }}
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 {{ app()->getLocale() === 'ar' ? 'right-0 pr-3.5' : 'left-0 pl-3.5' }} flex items-center text-slate-400 dark:text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </span>
                            <input type="password" name="password" id="password" required autocomplete="current-password"
                                class="erp-input w-full dark:bg-slate-900 border-slate-200 dark:border-slate-800 {{ app()->getLocale() === 'ar' ? 'pr-11' : 'pl-11' }} text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500" 
                                placeholder="••••••••">
                        </div>
                    </div>

                    <!-- Remember Me Option -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 select-none cursor-pointer">
                            <input type="checkbox" name="remember" id="remember" class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 transition-colors">
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                                {{ app()->getLocale() === 'ar' ? 'تذكرني على هذا الجهاز' : 'Remember me on this device' }}
                            </span>
                        </label>
                    </div>

                    <!-- Submit Login Button with Dynamic Loading Spinners -->
                    <button type="submit" id="submit-btn" class="w-full btn-primary font-bold shadow-lg shadow-brand-500/10 h-11 flex items-center justify-center gap-2 group transition-all duration-200">
                        <!-- Default text and icon -->
                        <span id="submit-text">{{ app()->getLocale() === 'ar' ? 'تسجيل الدخول الآمن' : 'Secure Sign In' }}</span>
                        <svg id="submit-icon" class="w-4 h-4 shrink-0 transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        
                        <!-- Loading spinner (Hidden initially) -->
                        <svg id="submit-spinner" class="hidden animate-spin h-5 h-5 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Mobile Footer Details -->
            <div class="lg:hidden text-center text-xs text-slate-400 pt-8 border-t border-slate-100 dark:border-slate-900 mt-8">
                <span>© {{ date('Y') }} Nawwat ERP.</span>
            </div>
        </div>

    </div>

    <!-- JS Micro-interactions & Loading State script -->
    <script>
        document.getElementById('login-form').addEventListener('submit', function (e) {
            const btn = document.getElementById('submit-btn');
            const txt = document.getElementById('submit-text');
            const icon = document.getElementById('submit-icon');
            const spinner = document.getElementById('submit-spinner');

            // Disable button to prevent double-submit
            btn.disabled = true;
            btn.classList.add('opacity-80', 'cursor-not-allowed');

            // Toggle spinner visibility
            txt.innerText = "{{ app()->getLocale() === 'ar' ? 'جاري التحقق...' : 'Signing in...' }}";
            icon.classList.add('hidden');
            spinner.classList.remove('hidden');
        });
    </script>
</body>
</html>
