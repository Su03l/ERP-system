@props([
    'title' => null,
    'description' => null,
    'resetUrl' => null,
])

@php
    $defaultTitle = app()->getLocale() === 'ar' ? 'لم نجد أي نتائج مطابقة' : 'No Matching Results Found';
    $defaultDescription = app()->getLocale() === 'ar' ? 'يرجى التحقق من الكلمات الدلالية أو الفلاتر النشطة والمحاولة مرة أخرى بقيم مختلفة.' : 'Please check your search keywords, spelling, or active filters and try again with alternative terms.';
@endphp

<div class="erp-card p-10 text-center flex flex-col items-center justify-center border border-slate-100 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900 shadow-premium">
    <div class="w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center mb-4 shrink-0 shadow-sm">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
    </div>

    <h4 class="font-bold text-slate-800 dark:text-white text-sm mb-1">
        {{ $title ?? $defaultTitle }}
    </h4>
    
    <p class="text-xs text-slate-400 dark:text-slate-500 max-w-sm leading-relaxed mb-5">
        {{ $description ?? $defaultDescription }}
    </p>

    @if($resetUrl)
        <div>
            <a href="{{ $resetUrl }}" class="btn-secondary text-xs py-1.5 px-4 font-bold shadow-sm flex items-center gap-1.5 hover:text-brand-600 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                <span>{{ app()->getLocale() === 'ar' ? 'إعادة ضبط البحث' : 'Clear Search / Filters' }}</span>
            </a>
        </div>
    @endif
</div>
