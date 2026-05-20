<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ app()->getLocale() === 'ar' ? 'نواة ERP - الصفحة الرئيسية' : 'Nawwat ERP - Home' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 antialiased font-sans min-h-screen flex flex-col items-center justify-center relative overflow-hidden">
    
    <!-- Background decorations -->
    <div class="absolute top-0 right-0 w-[500px] h-[500px] rounded-full bg-brand-500/10 blur-[120px] -mr-40 -mt-40 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-[400px] h-[400px] rounded-full bg-emerald-500/10 blur-[100px] -ml-40 -mb-40 pointer-events-none"></div>

    <div class="z-10 text-center max-w-3xl px-6">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-tr from-brand-600 to-emerald-400 shadow-xl shadow-brand-500/20 mb-8 mx-auto">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"></path>
            </svg>
        </div>
        
        <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight text-slate-900 dark:text-white mb-6 leading-tight">
            {{ app()->getLocale() === 'ar' ? 'نواة ERP' : 'Nawwat ERP' }}
        </h1>
        
        <p class="text-lg md:text-xl text-slate-600 dark:text-slate-400 mb-10 leading-relaxed max-w-2xl mx-auto">
            {{ app()->getLocale() === 'ar' ? 'النظام السحابي المتكامل لإدارة أعمالك بكفاءة وأمان تام.' : 'The integrated cloud system to manage your business efficiently and securely.' }}
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-primary px-8 py-3 text-lg font-bold w-full sm:w-auto text-center rounded-xl shadow-lg shadow-brand-500/25 hover:shadow-brand-500/40 transition-all">
                        {{ app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard' }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-primary px-8 py-3 text-lg font-bold w-full sm:w-auto text-center rounded-xl shadow-lg shadow-brand-500/25 hover:shadow-brand-500/40 transition-all">
                        {{ app()->getLocale() === 'ar' ? 'تسجيل الدخول' : 'Log In' }}
                    </a>
                    
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-secondary px-8 py-3 text-lg font-bold w-full sm:w-auto text-center rounded-xl transition-all">
                            {{ app()->getLocale() === 'ar' ? 'إنشاء حساب' : 'Register' }}
                        </a>
                    @endif
                @endauth
            @endif
        </div>
        
        <div class="mt-12 text-sm font-medium">
            <div class="flex items-center justify-center gap-4">
                <a href="?locale=ar" class="{{ app()->getLocale() === 'ar' ? 'text-brand-600 dark:text-brand-400' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }} transition-colors font-arabic">العربية</a>
                <span class="text-slate-300 dark:text-slate-700">|</span>
                <a href="?locale=en" class="{{ app()->getLocale() === 'en' ? 'text-brand-600 dark:text-brand-400' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-300' }} transition-colors">English</a>
            </div>
        </div>
    </div>
</body>
</html>
