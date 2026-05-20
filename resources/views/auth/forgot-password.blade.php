<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full bg-slate-50 dark:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ app()->getLocale() === 'ar' ? 'استعادة كلمة المرور - نواة ERP' : 'Forgot Password - Nawwat ERP' }}</title>

    @fonts

    <!-- Scripts and Stylesheets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full text-slate-800 dark:text-slate-200 antialiased overflow-hidden bg-slate-50 dark:bg-slate-950 font-sans flex items-center justify-center p-4">
    
    <!-- Outer Glow Ambient Light Orbs -->
    <div class="absolute top-1/2 left-1/2 w-[400px] h-[400px] rounded-full bg-brand-500/5 blur-[100px] -translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>

    <div class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl shadow-premium-lg p-8 sm:p-10 relative z-10">
        
        <!-- Header logo -->
        <div class="flex flex-col items-center text-center space-y-4 mb-8">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-brand-600 to-emerald-400 flex items-center justify-center shadow-lg shadow-brand-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
            </div>
            <div class="space-y-2">
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    {{ app()->getLocale() === 'ar' ? 'استعادة كلمة المرور' : 'Forgot Password' }}
                </h1>
                <p class="text-slate-500 dark:text-slate-400 text-xs">
                    {{ app()->getLocale() === 'ar' ? 'أدخل بريدك الإلكتروني وسنقوم بإرسال رابط لتحديث كلمة المرور.' : 'Provide your registered email and we will send a password reset link.' }}
                </p>
            </div>
        </div>

        <!-- Validation Error Banner -->
        @if ($errors->any())
            <div class="p-4 mb-6 rounded-xl bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/50 flex items-start gap-3 text-rose-800 dark:text-rose-400 text-xs" role="alert">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <ul class="list-disc {{ app()->getLocale() === 'ar' ? 'pr-4' : 'pl-4' }} space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Session Status Banner -->
        @if (session('success'))
            <div class="p-4 mb-6 rounded-xl bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/50 flex items-center gap-2 text-xs font-semibold text-emerald-800 dark:text-emerald-400 animate-pulse" role="alert">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Forgot Password Form -->
        <form method="POST" action="{{ route('password.email') }}" class="space-y-6" id="forgot-form">
            @csrf

            <!-- Email Address Input -->
            <div class="space-y-2">
                <label for="email" class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider block">
                    {{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني المسجل' : 'Registered Email Address' }}
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 {{ app()->getLocale() === 'ar' ? 'right-0 pr-3.5' : 'left-0 pl-3.5' }} flex items-center text-slate-400 dark:text-slate-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"></path></svg>
                    </span>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                        class="erp-input w-full dark:bg-slate-950 border-slate-200 dark:border-slate-800 {{ app()->getLocale() === 'ar' ? 'pr-11' : 'pl-11' }} text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500" 
                        placeholder="name@company.com">
                </div>
            </div>

            <!-- Submit button -->
            <button type="submit" id="submit-btn" class="w-full btn-primary font-bold shadow-lg shadow-brand-500/10 h-11 flex items-center justify-center gap-2 group transition-all duration-200">
                <span id="submit-text">{{ app()->getLocale() === 'ar' ? 'إرسال رابط الاستعادة' : 'Send Recovery Link' }}</span>
                
                <!-- Loading Spinner -->
                <svg id="submit-spinner" class="hidden animate-spin h-5 h-5 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </form>

        <!-- Back to Sign In Link -->
        <div class="mt-6 text-center border-t border-slate-100 dark:border-slate-800 pt-6">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-brand-500 transition-colors">
                <svg class="w-4 h-4 {{ app()->getLocale() === 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>{{ app()->getLocale() === 'ar' ? 'العودة لصفحة تسجيل الدخول' : 'Back to Login Screen' }}</span>
            </a>
        </div>

    </div>

    <script>
        document.getElementById('forgot-form').addEventListener('submit', function (e) {
            const btn = document.getElementById('submit-btn');
            const txt = document.getElementById('submit-text');
            const spinner = document.getElementById('submit-spinner');

            btn.disabled = true;
            btn.classList.add('opacity-80', 'cursor-not-allowed');

            txt.innerText = "{{ app()->getLocale() === 'ar' ? 'جاري الإرسال...' : 'Sending link...' }}";
            spinner.classList.remove('hidden');
        });
    </script>
</body>
</html>
