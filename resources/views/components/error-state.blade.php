@props([
    'title' => null,
    'description' => null,
    'code' => null,
])

@php
    $defaultTitle = app()->getLocale() === 'ar' ? 'حدث خطأ في النظام' : 'An Unexpected Error Occurred';
    $defaultDescription = app()->getLocale() === 'ar' ? 'نعتذر عن هذا الخلل الموقّت. حدثت مشكلة أثناء محاولة الاتصال بخوادم قاعدة البيانات أو جلب البيانات المطلوبة.' : 'We apologize for this temporary issue. There was a problem communicating with our enterprise servers. Our administrators have been notified.';
@endphp

<div class="erp-card p-12 text-center flex flex-col items-center justify-center border border-amber-100 dark:border-amber-950/20 rounded-2xl bg-white dark:bg-slate-900 shadow-lg">
    <div class="w-16 h-16 rounded-full bg-amber-50 dark:bg-amber-950/30 text-amber-500 dark:text-amber-400 flex items-center justify-center mb-5 shrink-0 shadow-sm border border-amber-100 dark:border-amber-900/50">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
    </div>

    @if($code)
        <span class="px-2.5 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-500 rounded text-[10px] font-mono font-bold mb-2">
            ERR_CODE: {{ $code }}
        </span>
    @endif

    <h3 class="font-bold text-slate-800 dark:text-white text-base mb-2">
        {{ $title ?? $defaultTitle }}
    </h3>
    
    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md leading-relaxed mb-6">
        {{ $description ?? $defaultDescription }}
    </p>

    <div class="flex items-center gap-3">
        <button onclick="window.location.reload()" class="btn-primary text-xs font-bold shadow-sm">
            {{ app()->getLocale() === 'ar' ? 'تحديث الصفحة' : 'Refresh Page' }}
        </button>
        <a href="{{ url('/dashboard') }}" class="btn-secondary text-xs font-bold shadow-sm">
            {{ app()->getLocale() === 'ar' ? 'الرجوع للرئيسية' : 'Go to Dashboard' }}
        </a>
    </div>
</div>
