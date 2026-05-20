@props([
    'title' => null,
    'description' => null,
])

@php
    $defaultTitle = app()->getLocale() === 'ar' ? 'عفواً، لا تملك الصلاحية للوصول' : 'Access Denied / Unauthorized';
    $defaultDescription = app()->getLocale() === 'ar' ? 'حسابك الحالي لا يملك أذونات الدور الكافية لعرض هذه الصفحة أو تنفيذ هذا الإجراء. يرجى مراجعة مسؤول النظام أو مدير الشركة.' : 'Your profile lacks the required role permissions to access this module or perform this action. Please contact your company administrator.';
@endphp

<div class="erp-card p-12 text-center flex flex-col items-center justify-center rounded-2xl bg-white dark:bg-slate-900 border border-rose-100 dark:border-rose-950/20 shadow-md">
    <div class="w-16 h-16 rounded-full bg-rose-50 dark:bg-rose-950/30 text-rose-500 dark:text-rose-400 flex items-center justify-center mb-5 shrink-0 shadow-sm border border-rose-100 dark:border-rose-900/50">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
        </svg>
    </div>

    <h3 class="font-bold text-rose-800 dark:text-rose-400 text-base mb-2">
        {{ $title ?? $defaultTitle }}
    </h3>
    
    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-lg leading-relaxed mb-6">
        {{ $description ?? $defaultDescription }}
    </p>

    <div class="flex items-center gap-3">
        <button onclick="window.history.back()" class="btn-secondary text-xs font-bold shadow-sm">
            {{ app()->getLocale() === 'ar' ? 'الرجوع للخلف' : 'Go Back' }}
        </button>
        <a href="{{ url('/dashboard') }}" class="btn-primary text-xs font-bold shadow-sm">
            {{ app()->getLocale() === 'ar' ? 'الرئيسية' : 'Dashboard' }}
        </a>
    </div>
</div>
