<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full bg-slate-50 dark:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ app()->getLocale() === 'ar' ? 'تأكيد الحساب - نواة ERP' : 'Verify Email - Nawwat ERP' }}</title>

    @fonts

    <!-- Scripts and Stylesheets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full text-slate-800 dark:text-slate-200 antialiased overflow-hidden bg-slate-50 dark:bg-slate-950 font-sans flex items-center justify-center p-4">
    
    <!-- Ambient ambient glow -->
    <div class="absolute top-1/2 left-1/2 w-[400px] h-[400px] rounded-full bg-brand-500/5 blur-[100px] -translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>

    <div class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl shadow-premium-lg p-8 sm:p-10 relative z-10">
        
        <!-- Header -->
        <div class="flex flex-col items-center text-center space-y-4 mb-8">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-brand-600 to-emerald-400 flex items-center justify-center shadow-lg shadow-brand-500/20">
                <svg class="w-6 h-6 text-white animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 19v-8.93a2 2 0 01.89-1.664l8-5.333a2 2 0 012.22 0l8 5.333A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-2.25-1.5a2 2 0 00-2.22 0l-2.25 1.5"></path>
                </svg>
            </div>
            <div class="space-y-2">
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    {{ app()->getLocale() === 'ar' ? 'التحقق من البريد الإلكتروني' : 'Verify Your Email' }}
                </h1>
                <p class="text-slate-500 dark:text-slate-400 text-xs">
                    {{ app()->getLocale() === 'ar' ? 'شكراً لتسجيلك! قبل البدء، يرجى النقر على الرابط الذي أرسلناه للتو للتحقق من بريدك الإلكتروني.' : 'Thanks for signing up! Before getting started, please verify your email address by clicking the link we just emailed you.' }}
                </p>
            </div>
        </div>

        <!-- Session Status Banner -->
        @if (session('success'))
            <div class="p-4 mb-6 rounded-xl bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/50 flex items-center gap-2 text-xs font-semibold text-emerald-800 dark:text-emerald-400" role="alert">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="space-y-4">
            <!-- Resend Form -->
            <form method="POST" action="{{ route('verification.send') }}" id="verify-form">
                @csrf
                <button type="submit" id="submit-btn" class="w-full btn-primary font-bold shadow-lg shadow-brand-500/10 h-11 flex items-center justify-center gap-2 group transition-all duration-200">
                    <span id="submit-text">{{ app()->getLocale() === 'ar' ? 'إعادة إرسال رابط التحقق' : 'Resend Verification Email' }}</span>
                    
                    <!-- Loading Spinner -->
                    <svg id="submit-spinner" class="hidden animate-spin h-5 h-5 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>

            <!-- Sign Out action -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full btn-secondary text-xs h-10 flex items-center justify-center font-bold">
                    {{ app()->getLocale() === 'ar' ? 'تسجيل الخروج من الحساب' : 'Log Out from Workspace' }}
                </button>
            </form>
        </div>

    </div>

    <script>
        document.getElementById('verify-form').addEventListener('submit', function (e) {
            const btn = document.getElementById('submit-btn');
            const txt = document.getElementById('submit-text');
            const spinner = document.getElementById('submit-spinner');

            btn.disabled = true;
            btn.classList.add('opacity-80', 'cursor-not-allowed');

            txt.innerText = "{{ app()->getLocale() === 'ar' ? 'جاري الإرسال...' : 'Sending email...' }}";
            spinner.classList.remove('hidden');
        });
    </script>
</body>
</html>
