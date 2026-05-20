@props([
    'title' => '',
    'value' => null,
    'formattedValue' => null,
    'trend' => null,
    'comparisonValue' => null,
    'module' => 'general',
    'loading' => false,
    'empty' => false,
])

@php
    // Curated accent themes based on the KPI category
    $theme = match(strtolower($module)) {
        'hr' => [
            'bg' => 'bg-teal-50 dark:bg-teal-950/20',
            'text' => 'text-teal-600 dark:text-teal-400',
            'border' => 'hover:border-teal-500/30 dark:hover:border-teal-500/20',
            'badge' => 'bg-teal-500/10 text-teal-700 dark:text-teal-400',
            'label' => app()->getLocale() === 'ar' ? 'الموارد البشرية' : 'HR',
        ],
        'payroll' => [
            'bg' => 'bg-violet-50 dark:bg-violet-950/20',
            'text' => 'text-violet-600 dark:text-violet-400',
            'border' => 'hover:border-violet-500/30 dark:hover:border-violet-500/20',
            'badge' => 'bg-violet-500/10 text-violet-700 dark:text-violet-400',
            'label' => app()->getLocale() === 'ar' ? 'الرواتب' : 'Payroll',
        ],
        'finance', 'accounting' => [
            'bg' => 'bg-cyan-50 dark:bg-cyan-950/20',
            'text' => 'text-cyan-600 dark:text-cyan-400',
            'border' => 'hover:border-cyan-500/30 dark:hover:border-cyan-500/20',
            'badge' => 'bg-cyan-500/10 text-cyan-700 dark:text-cyan-400',
            'label' => app()->getLocale() === 'ar' ? 'المالية والنشاط' : 'Finance',
        ],
        'saas' => [
            'bg' => 'bg-amber-50 dark:bg-amber-950/20',
            'text' => 'text-amber-600 dark:text-amber-400',
            'border' => 'hover:border-amber-500/30 dark:hover:border-amber-500/20',
            'badge' => 'bg-amber-500/10 text-amber-700 dark:text-amber-400',
            'label' => app()->getLocale() === 'ar' ? 'الاشتراكات' : 'SaaS',
        ],
        default => [
            'bg' => 'bg-slate-50 dark:bg-slate-900/40',
            'text' => 'text-slate-600 dark:text-slate-400',
            'border' => 'hover:border-slate-500/20 dark:hover:border-slate-500/10',
            'badge' => 'bg-slate-500/10 text-slate-700 dark:text-slate-400',
            'label' => app()->getLocale() === 'ar' ? 'عام' : 'General',
        ],
    };

    $renderedValue = $formattedValue ?? $value;
    $isEmpty = $empty || ($value === null && $formattedValue === null);
@endphp

<div {{ $attributes->merge(['class' => 'erp-card p-6 flex flex-col justify-between overflow-hidden border border-slate-200/50 dark:border-slate-800/60 transition-all duration-300 ' . $theme['border']]) }}>
    @if($loading)
        <!-- ==================== LOADING SKELETON STATE ==================== -->
        <div class="space-y-4 animate-pulse w-full">
            <div class="flex items-center justify-between">
                <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-1/2"></div>
                <div class="h-5 bg-slate-200 dark:bg-slate-800 rounded-full w-12"></div>
            </div>
            <div class="space-y-2">
                <div class="h-8 bg-slate-200 dark:bg-slate-800 rounded w-1/3"></div>
                <div class="h-3 bg-slate-100 dark:bg-slate-800/50 rounded w-2/3"></div>
            </div>
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-16"></div>
                <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-12"></div>
            </div>
        </div>
    @else
        <!-- ==================== RENDERED STATE ==================== -->
        <div class="space-y-3.5">
            <!-- Header Section -->
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1.5 flex-1">
                    <div class="flex items-center flex-wrap gap-2">
                        <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                            {{ $title }}
                        </span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold {{ $theme['badge'] }}">
                            {{ $theme['label'] }}
                        </span>
                    </div>
                </div>
                
                <!-- Ambient Glowing Icon Wrapper -->
                <div class="w-11 h-11 rounded-xl {{ $theme['bg'] }} {{ $theme['text'] }} flex items-center justify-center shrink-0 shadow-sm">
                    @if(isset($icon))
                        {{ $icon }}
                    @else
                        <!-- Default accent-based icon fallbacks -->
                        @if(strtolower($module) === 'hr')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        @elseif(strtolower($module) === 'payroll')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        @elseif(in_array(strtolower($module), ['finance', 'accounting']))
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        @elseif(strtolower($module) === 'saas')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Value Section / Empty State -->
            <div class="space-y-1">
                @if($isEmpty)
                    <!-- Empty/No Data display state -->
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-extrabold text-slate-300 dark:text-slate-700 tracking-tight select-none">—</span>
                        <span class="text-xs text-slate-400 dark:text-slate-500 font-semibold select-none">
                            ({{ app()->getLocale() === 'ar' ? 'لا توجد بيانات' : 'No data' }})
                        </span>
                    </div>
                @else
                    <!-- Active values display state -->
                    <h3 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight leading-none">
                        {{ $renderedValue }}
                    </h3>
                @endif
            </div>

            <!-- Footer Context / Trend indicators -->
            <div class="mt-4 pt-3.5 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                @if(!$isEmpty && $trend !== null)
                    @php
                        $isUp = strtolower($trend) === 'up';
                        $isDown = strtolower($trend) === 'down';
                        $trendColor = $isUp ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/20' : ($isDown ? 'text-rose-600 bg-rose-50 dark:bg-rose-950/20' : 'text-slate-500 bg-slate-50 dark:bg-slate-900');
                    @endphp
                    <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full {{ $trendColor }}">
                        @if($isUp)
                            <svg class="w-3 h-3 mr-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        @elseif($isDown)
                            <svg class="w-3 h-3 mr-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
                        @else
                            <svg class="w-3 h-3 mr-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3"></path></svg>
                        @endif
                        <span>{{ $comparisonValue ?? '0%' }}</span>
                    </span>
                @else
                    <span class="text-[10px] text-slate-400 font-medium">
                        {{ app()->getLocale() === 'ar' ? 'مؤشر أداء مباشر' : 'Live performance metrics' }}
                    </span>
                @endif
                
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider select-none">
                    {{ $theme['label'] }}
                </span>
            </div>
        @endif
    </div>
</div>
