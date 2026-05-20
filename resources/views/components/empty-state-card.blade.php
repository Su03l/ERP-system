@props([
    'title' => null,
    'description' => null,
    'icon' => null,
    'actionLink' => null,
    'actionText' => null,
])

@php
    $defaultTitle = app()->getLocale() === 'ar' ? 'لا توجد بيانات متاحة' : 'No Data Available';
    $defaultDescription = app()->getLocale() === 'ar' ? 'لم نتمكن من العثور على أي سجلات في هذا القسم حالياً.' : 'There are no active records found in this section.';
@endphp

<div class="erp-card p-12 text-center flex flex-col items-center justify-center border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl bg-white dark:bg-slate-900 transition-all hover:border-slate-300 dark:hover:border-slate-700">
    <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center mb-4 shrink-0 shadow-sm border border-slate-100 dark:border-slate-800">
        @if($icon)
            {!! $icon !!}
        @else
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        @endif
    </div>

    <h3 class="font-bold text-slate-800 dark:text-white text-base mb-1">
        {{ $title ?? $defaultTitle }}
    </h3>
    
    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm leading-relaxed mb-6">
        {{ $description ?? $defaultDescription }}
    </p>

    @if($actionLink && $actionText)
        <div>
            <a href="{{ $actionLink }}" class="btn-primary shadow-md shadow-brand-500/10 active:scale-98 transition-transform font-bold text-xs px-4 py-2 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>{{ $actionText }}</span>
            </a>
        </div>
    @elseif(isset($action))
        <div>
            {{ $action }}
        </div>
    @endif
</div>
