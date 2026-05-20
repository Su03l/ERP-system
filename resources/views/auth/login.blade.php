<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ app()->getLocale() === 'ar' ? 'تسجيل الدخول - نواة ERP' : 'Log In - Nawwat ERP' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 antialiased min-h-screen flex items-center justify-center p-4 md:p-8 font-sans relative overflow-hidden">
    
    <!-- Background Blobs -->
    <div class="absolute top-0 right-0 w-[500px] h-[500px] rounded-full bg-brand-500/10 blur-[120px] -mr-40 -mt-40 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-[400px] h-[400px] rounded-full bg-emerald-500/10 blur-[100px] -ml-40 -mb-40 pointer-events-none"></div>

    <div class="w-full max-w-md z-10 relative">
        
        <!-- Logo -->
        <div class="flex flex-col items-center mb-8">
            <a href="{{ url('/') }}" class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-600 to-emerald-400 flex items-center justify-center shadow-lg shadow-brand-500/20 mb-4 transition-transform hover:scale-105">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"></path>
                </svg>
            </a>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white text-center">
                {{ app()->getLocale() === 'ar' ? 'نواة ERP' : 'Nawwat ERP' }}
            </h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-2 text-center">
                {{ app()->getLocale() === 'ar' ? 'تسجيل الدخول للمنصة السحابية' : 'Sign in to your cloud workspace' }}
            </p>
        </div>

        <div class="erp-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none rounded-2xl p-6 md:p-8">
            
            <!-- Language Toggle -->
            <div class="flex justify-end mb-6">
                <div class="inline-flex rounded-lg bg-slate-100 dark:bg-slate-800 p-1">
                    <a href="?locale=ar" class="{{ app()->getLocale() === 'ar' ? 'bg-white dark:bg-slate-700 shadow-sm text-brand-600 dark:text-brand-400' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }} px-3 py-1.5 rounded-md text-xs font-bold transition-all font-arabic">
                        العربية
                    </a>
                    <a href="?locale=en" class="{{ app()->getLocale() === 'en' ? 'bg-white dark:bg-slate-700 shadow-sm text-brand-600 dark:text-brand-400' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }} px-3 py-1.5 rounded-md text-xs font-bold transition-all">
                        EN
                    </a>
                </div>
            </div>

            <!-- Errors -->
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/50">
                    <ul class="list-disc {{ app()->getLocale() === 'ar' ? 'pr-4' : 'pl-4' }} space-y-1 text-xs font-medium text-rose-600 dark:text-rose-400">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Success -->
            @if (session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/50 text-emerald-600 dark:text-emerald-400 text-xs font-medium">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5" id="login-form">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email Address' }}
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 {{ app()->getLocale() === 'ar' ? 'right-0 pr-3.5' : 'left-0 pl-3.5' }} flex items-center text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"></path></svg>
                        </span>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                            class="erp-input w-full {{ app()->getLocale() === 'ar' ? 'pr-11' : 'pl-11' }} py-2.5" 
                            placeholder="name@company.com" dir="ltr">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="text-sm font-bold text-slate-700 dark:text-slate-300">
                            {{ app()->getLocale() === 'ar' ? 'كلمة المرور' : 'Password' }}
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-semibold text-brand-500 hover:text-brand-600 dark:text-brand-400 dark:hover:text-brand-300 transition-colors">
                                {{ app()->getLocale() === 'ar' ? 'نسيت كلمة المرور؟' : 'Forgot password?' }}
                            </a>
                        @endif
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 {{ app()->getLocale() === 'ar' ? 'right-0 pr-3.5' : 'left-0 pl-3.5' }} flex items-center text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </span>
                        <input type="password" name="password" id="password" required 
                            class="erp-input w-full {{ app()->getLocale() === 'ar' ? 'pr-11' : 'pl-11' }} py-2.5" 
                            placeholder="••••••••" dir="ltr">
                    </div>
                </div>

                <div class="flex items-center">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" id="remember" class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 transition-colors">
                        <span class="text-sm font-medium text-slate-600 dark:text-slate-400">
                            {{ app()->getLocale() === 'ar' ? 'تذكرني' : 'Remember me' }}
                        </span>
                    </label>
                </div>

                <button type="submit" id="submit-btn" class="w-full btn-primary font-bold shadow-lg shadow-brand-500/20 hover:shadow-brand-500/40 h-12 flex items-center justify-center gap-2 group transition-all rounded-xl mt-4 text-base">
                    <span id="submit-text">{{ app()->getLocale() === 'ar' ? 'تسجيل الدخول' : 'Sign In' }}</span>
                    <svg id="submit-icon" class="w-5 h-5 shrink-0 transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    
                    <svg id="submit-spinner" class="hidden animate-spin h-5 w-5 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>
        </div>
        
        <p class="text-center text-xs text-slate-400 mt-8">
            © {{ date('Y') }} Nawwat ERP. {{ app()->getLocale() === 'ar' ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}
        </p>
    </div>

    <script>
        document.getElementById('login-form').addEventListener('submit', function () {
            const btn = document.getElementById('submit-btn');
            btn.disabled = true;
            btn.classList.add('opacity-80', 'cursor-not-allowed');
            document.getElementById('submit-text').innerText = "{{ app()->getLocale() === 'ar' ? 'جاري التحقق...' : 'Signing in...' }}";
            document.getElementById('submit-icon').classList.add('hidden');
            document.getElementById('submit-spinner').classList.remove('hidden');
        });
    </script>
</body>
</html>
