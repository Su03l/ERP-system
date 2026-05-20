@props([
    'title' => '',
    'type' => 'bar', // 'line', 'bar', 'donut', 'area'
    'module' => 'general',
    'loading' => false,
    'empty' => false,
    'labels' => [], // e.g. ['Jan', 'Feb', 'Mar']
    'values' => [], // e.g. [120, 200, 150]
    'height' => 'h-72',
])

@php
    // Curated HSL themes matching dashboard colors
    $theme = match(strtolower($module)) {
        'hr' => [
            'bg' => 'bg-teal-50/50 dark:bg-teal-950/10',
            'text' => 'text-teal-600 dark:text-teal-400',
            'border' => 'hover:border-teal-500/30 dark:hover:border-teal-500/20',
            'badge' => 'bg-teal-500/10 text-teal-700 dark:text-teal-400',
            'label' => app()->getLocale() === 'ar' ? 'الموارد البشرية' : 'HR',
            'gradient' => 'from-teal-500 to-emerald-400 dark:from-teal-600 dark:to-emerald-500',
            'color' => '#0d9488',
            'fill' => 'rgba(13, 148, 136, 0.1)',
        ],
        'payroll' => [
            'bg' => 'bg-violet-50/50 dark:bg-violet-950/10',
            'text' => 'text-violet-600 dark:text-violet-400',
            'border' => 'hover:border-violet-500/30 dark:hover:border-violet-500/20',
            'badge' => 'bg-violet-500/10 text-violet-700 dark:text-violet-400',
            'label' => app()->getLocale() === 'ar' ? 'الرواتب' : 'Payroll',
            'gradient' => 'from-violet-500 to-fuchsia-400 dark:from-violet-600 dark:to-fuchsia-500',
            'color' => '#7c3aed',
            'fill' => 'rgba(124, 58, 237, 0.1)',
        ],
        'finance', 'accounting' => [
            'bg' => 'bg-cyan-50/50 dark:bg-cyan-950/10',
            'text' => 'text-cyan-600 dark:text-cyan-400',
            'border' => 'hover:border-cyan-500/30 dark:hover:border-cyan-500/20',
            'badge' => 'bg-cyan-500/10 text-cyan-700 dark:text-cyan-400',
            'label' => app()->getLocale() === 'ar' ? 'المالية والنشاط' : 'Finance',
            'gradient' => 'from-cyan-500 to-blue-400 dark:from-cyan-600 dark:to-blue-500',
            'color' => '#0891b2',
            'fill' => 'rgba(8, 145, 178, 0.1)',
        ],
        'saas' => [
            'bg' => 'bg-amber-50/50 dark:bg-amber-950/10',
            'text' => 'text-amber-600 dark:text-amber-400',
            'border' => 'hover:border-amber-500/30 dark:hover:border-amber-500/20',
            'badge' => 'bg-amber-500/10 text-amber-700 dark:text-amber-400',
            'label' => app()->getLocale() === 'ar' ? 'الاشتراكات' : 'SaaS',
            'gradient' => 'from-amber-500 to-orange-400 dark:from-amber-600 dark:to-orange-500',
            'color' => '#d97706',
            'fill' => 'rgba(217, 119, 6, 0.1)',
        ],
        default => [
            'bg' => 'bg-slate-50/50 dark:bg-slate-900/20',
            'text' => 'text-slate-600 dark:text-slate-400',
            'border' => 'hover:border-slate-500/20 dark:hover:border-slate-500/10',
            'badge' => 'bg-slate-500/10 text-slate-700 dark:text-slate-400',
            'label' => app()->getLocale() === 'ar' ? 'عام' : 'General',
            'gradient' => 'from-brand-500 to-slate-400 dark:from-brand-600 dark:to-slate-505',
            'color' => '#475569',
            'fill' => 'rgba(71, 85, 105, 0.1)',
        ],
    };

    $isEmpty = $empty || empty($values) || empty($labels);

    // Coordinate Math for SVG Line/Area Charts
    $svgPath = '';
    $areaPath = '';
    $points = [];
    $svgWidth = 500;
    $svgHeight = 180;
    $paddingX = 30;
    $paddingY = 20;

    if (!$isEmpty && (strtolower($type) === 'line' || strtolower($type) === 'area')) {
        $count = count($values);
        $max = max($values) ?: 1;
        $min = min($values) ?: 0;
        $range = ($max - $min) ?: 1;

        for ($i = 0; $i < $count; $i++) {
            $x = $count > 1 
                ? $paddingX + ($i * (($svgWidth - 2 * $paddingX) / ($count - 1))) 
                : ($svgWidth / 2);
            $y = $svgHeight - $paddingY - (($values[$i] - $min) / $range) * ($svgHeight - 2 * $paddingY);
            $points[] = "$x,$y";
        }

        if (count($points) > 0) {
            $svgPath = "M " . implode(" L ", $points);
            $areaPath = $svgPath . " L " . ($svgWidth - $paddingX) . " " . ($svgHeight - $paddingY) . " L $paddingX " . ($svgHeight - $paddingY) . " Z";
        }
    }

    // Math for SVG Donut Charts
    $donutSegments = [];
    if (!$isEmpty && strtolower($type) === 'donut') {
        $total = array_sum($values) ?: 1;
        $cumulativePercent = 0;
        $radius = 70;
        $circumference = 2 * pi() * $radius; // ~439.8

        $colorList = [
            'stroke-brand-500 dark:stroke-brand-400',
            'stroke-teal-500 dark:stroke-teal-400',
            'stroke-violet-500 dark:stroke-violet-400',
            'stroke-amber-500 dark:stroke-amber-400',
            'stroke-rose-500 dark:stroke-rose-400',
            'stroke-indigo-500 dark:stroke-indigo-400'
        ];

        $bgList = [
            'bg-brand-500', 'bg-teal-500', 'bg-violet-500', 'bg-amber-500', 'bg-rose-500', 'bg-indigo-500'
        ];

        foreach ($values as $index => $val) {
            $percent = $val / $total;
            $dashArray = ($percent * $circumference) . ' ' . $circumference;
            $dashOffset = -($cumulativePercent * $circumference);
            $cumulativePercent += $percent;

            $donutSegments[] = [
                'dashArray' => $dashArray,
                'dashOffset' => $dashOffset,
                'color' => $colorList[$index % count($colorList)],
                'bgColor' => $bgList[$index % count($bgList)],
                'label' => $labels[$index],
                'value' => $val,
                'percentage' => round($percent * 100, 1),
            ];
        }
    }
@endphp

<div {{ $attributes->merge(['class' => 'erp-card p-6 flex flex-col justify-between overflow-hidden border border-slate-200/50 dark:border-slate-800/60 transition-all duration-300 ' . $theme['border']]) }}>
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-4 gap-4">
        <div class="flex items-center gap-2">
            <h4 class="font-bold text-slate-800 dark:text-white text-sm tracking-tight">
                {{ $title }}
            </h4>
            <span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold {{ $theme['badge'] }}">
                {{ $theme['label'] }}
            </span>
        </div>
        
        @if(isset($actions))
            <div class="flex items-center gap-2 shrink-0">
                {{ $actions }}
            </div>
        @endif
    </div>

    <!-- Main Chart Window -->
    <div class="relative w-full flex-1 flex flex-col justify-center {{ $height }}">
        @if($loading)
            <!-- ==================== LOADING SKELETON STATE ==================== -->
            <div class="w-full h-full flex flex-col justify-between animate-pulse pt-4">
                @if(strtolower($type) === 'donut')
                    <div class="flex items-center justify-center h-full gap-8">
                        <div class="w-32 h-32 rounded-full border-8 border-slate-200 dark:border-slate-800"></div>
                        <div class="space-y-2 flex-1 max-w-[150px]">
                            <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-full"></div>
                            <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-5/6"></div>
                            <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-4/5"></div>
                        </div>
                    </div>
                @elseif(strtolower($type) === 'bar')
                    <div class="flex items-end justify-between h-40 px-2 gap-4">
                        <div class="h-24 bg-slate-200 dark:bg-slate-800 rounded-t-md w-full"></div>
                        <div class="h-36 bg-slate-200 dark:bg-slate-800 rounded-t-md w-full"></div>
                        <div class="h-16 bg-slate-200 dark:bg-slate-800 rounded-t-md w-full"></div>
                        <div class="h-28 bg-slate-200 dark:bg-slate-800 rounded-t-md w-full"></div>
                        <div class="h-32 bg-slate-200 dark:bg-slate-800 rounded-t-md w-full"></div>
                    </div>
                @else
                    <div class="w-full h-full flex flex-col justify-end relative">
                        <!-- Wave lines placeholder representation -->
                        <div class="absolute inset-x-0 bottom-8 h-24 border-t-2 border-dashed border-slate-200 dark:border-slate-800 flex flex-col justify-between py-2">
                            <div class="h-0.5 bg-slate-100 dark:bg-slate-900 w-full"></div>
                            <div class="h-0.5 bg-slate-100 dark:bg-slate-900 w-full"></div>
                        </div>
                        <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-3/4 mb-16 self-center"></div>
                    </div>
                @endif
            </div>
        @elseif($isEmpty)
            <!-- ==================== EMPTY / NO-DATA STATE ==================== -->
            <div class="w-full h-full flex flex-col items-center justify-center p-6 text-center select-none">
                <div class="w-14 h-14 rounded-full bg-slate-50 dark:bg-slate-900/50 flex items-center justify-center text-slate-300 dark:text-slate-700 mb-3 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 3.055A9.003 9.003 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                </div>
                <h5 class="text-xs font-bold text-slate-500 dark:text-slate-400">
                    {{ app()->getLocale() === 'ar' ? 'لا توجد بيانات متاحة حالياً' : 'No chart data available' }}
                </h5>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 max-w-[220px]">
                    {{ app()->getLocale() === 'ar' ? 'الرجاء التحقق من نطاق التواريخ أو تفعيل مؤشرات إضافية.' : 'Please check your date filters or check back later.' }}
                </p>
            </div>
        @else
            <!-- ==================== RENDERED STATE ==================== -->
            @if(strtolower($type) === 'bar')
                <!-- 1. RESPONSIVE BAR CHART -->
                @php
                    $maxBarValue = max($values) ?: 1;
                @endphp
                <div class="flex items-end justify-between h-full pt-4 pb-2 px-2 gap-3.5">
                    @foreach($values as $index => $val)
                        @php
                            $heightPercent = ($val / $maxBarValue) * 100;
                        @endphp
                        <div class="flex-1 flex flex-col items-center group cursor-pointer">
                            <div class="w-full relative flex flex-col items-center justify-end h-40">
                                <!-- Tooltip -->
                                <div class="absolute -top-7 bg-slate-950 dark:bg-slate-800 text-white text-[9px] font-black px-2 py-0.5 rounded shadow opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                    {{ number_format($val) }}
                                </div>
                                <!-- Vertical Bar with Gradient and Border Accent -->
                                <div class="w-full rounded-t-md bg-gradient-to-t {{ $theme['gradient'] }} opacity-85 group-hover:opacity-100 group-hover:shadow-md transition-all duration-300" 
                                     style="height: {{ max($heightPercent, 4) }}%">
                                </div>
                            </div>
                            <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 mt-2 truncate w-full text-center tracking-tight select-none">
                                {{ $labels[$index] }}
                            </span>
                        </div>
                    @endforeach
                </div>

            @elseif(strtolower($type) === 'donut')
                <!-- 2. HIGH-FIDELITY SVG DONUT CHART WITH SIDE LEGENDS -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-6 h-full py-2">
                    <div class="relative w-36 h-36 shrink-0">
                        <svg class="w-full h-full" viewBox="0 0 200 200">
                            <!-- Background ring -->
                            <circle cx="100" cy="100" r="70" fill="transparent" class="stroke-slate-100 dark:stroke-slate-800/60" stroke-width="20" />
                            
                            <!-- Dynamic Segment rings -->
                            @foreach($donutSegments as $segment)
                                <circle cx="100" cy="100" r="70" fill="transparent" 
                                        class="{{ $segment['color'] }} transition-all duration-300 hover:stroke-[22] cursor-pointer" 
                                        stroke-width="18" 
                                        stroke-dasharray="{{ $segment['dashArray'] }}" 
                                        stroke-dashoffset="{{ $segment['dashOffset'] }}" 
                                        transform="rotate(-90 100 100)" />
                            @endforeach
                        </svg>
                        <!-- Center info slot -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center select-none text-center">
                            <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                {{ app()->getLocale() === 'ar' ? 'الإجمالي' : 'Total' }}
                            </span>
                            <span class="text-lg font-black text-slate-800 dark:text-white mt-0.5 leading-none">
                                {{ number_format(array_sum($values)) }}
                            </span>
                        </div>
                    </div>

                    <!-- Compact Legend Lists -->
                    <div class="flex-1 space-y-2 w-full max-h-[160px] overflow-y-auto pr-1">
                        @foreach($donutSegments as $segment)
                            <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 dark:text-slate-300">
                                <div class="flex items-center gap-2 truncate">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ $segment['bgColor'] }}"></span>
                                    <span class="truncate">{{ $segment['label'] }}</span>
                                </div>
                                <span class="text-slate-400 font-bold shrink-0">
                                    {{ number_format($segment['value']) }} ({{ $segment['percentage'] }}%)
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

            @elseif(strtolower($type) === 'line' || strtolower($type) === 'area')
                <!-- 3. PREMIUM RESPONSIVE SVG WAVE LINE/AREA CHART -->
                <div class="w-full h-full flex flex-col justify-between pt-2">
                    <div class="relative flex-1">
                        <svg class="w-full h-full" viewBox="0 0 500 180" preserveAspectRatio="none">
                            <defs>
                                <!-- Flowing gradient fills corresponding to modules -->
                                <linearGradient id="gradient-teal" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#0d9488" stop-opacity="0.3"/>
                                    <stop offset="100%" stop-color="#0d9488" stop-opacity="0.0"/>
                                </linearGradient>
                                <linearGradient id="gradient-violet" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#7c3aed" stop-opacity="0.3"/>
                                    <stop offset="100%" stop-color="#7c3aed" stop-opacity="0.0"/>
                                </linearGradient>
                                <linearGradient id="gradient-cyan" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#0891b2" stop-opacity="0.3"/>
                                    <stop offset="100%" stop-color="#0891b2" stop-opacity="0.0"/>
                                </linearGradient>
                                <linearGradient id="gradient-amber" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#d97706" stop-opacity="0.3"/>
                                    <stop offset="100%" stop-color="#d97706" stop-opacity="0.0"/>
                                </linearGradient>
                                <linearGradient id="gradient-slate" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#475569" stop-opacity="0.3"/>
                                    <stop offset="100%" stop-color="#475569" stop-opacity="0.0"/>
                                </linearGradient>
                            </defs>

                            <!-- Horizontal Gridlines -->
                            <line x1="30" y1="20" x2="470" y2="20" stroke="currentColor" stroke-dasharray="3,3" class="text-slate-100 dark:text-slate-800/60" stroke-width="1" />
                            <line x1="30" y1="65" x2="470" y2="65" stroke="currentColor" stroke-dasharray="3,3" class="text-slate-100 dark:text-slate-800/60" stroke-width="1" />
                            <line x1="30" y1="110" x2="470" y2="110" stroke="currentColor" stroke-dasharray="3,3" class="text-slate-100 dark:text-slate-800/60" stroke-width="1" />
                            <line x1="30" y1="160" x2="470" y2="160" stroke="currentColor" stroke-dasharray="3,3" class="text-slate-200 dark:text-slate-800" stroke-width="1.2" />

                            @if(strtolower($type) === 'area')
                                <!-- Dynamic Glowing Area under wave -->
                                <path d="{{ $areaPath }}" fill="{{ $theme['fill'] }}" />
                            @endif

                            <!-- Smooth flowing line -->
                            <path d="{{ $svgPath }}" fill="none" stroke="{{ $theme['color'] }}" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" class="drop-shadow-sm" />

                            <!-- Interactive dots per data point -->
                            @foreach($points as $idx => $pt)
                                @php
                                    $coord = explode(',', $pt);
                                @endphp
                                <circle cx="{{ $coord[0] }}" cy="{{ $coord[1] }}" r="5" fill="{{ $theme['color'] }}" class="stroke-white dark:stroke-slate-900 cursor-pointer hover:r-7 transition-all duration-200" stroke-width="2" />
                            @endforeach
                        </svg>
                    </div>

                    <!-- Label legends along the x-axis -->
                    <div class="flex justify-between items-center px-6 mt-1.5 text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-tight select-none">
                        @foreach($labels as $lbl)
                            <span>{{ $lbl }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>

    <!-- Interactive Footer Summary -->
    <div class="mt-4 pt-3.5 border-t border-slate-100 dark:border-slate-800/60 flex items-center justify-between text-[10px] text-slate-400 font-bold uppercase tracking-wider select-none">
        @if(!$isEmpty)
            <span>
                {{ app()->getLocale() === 'ar' ? 'تحديث تلقائي للمخطط' : 'Live chart sync' }}
            </span>
        @else
            <span>
                {{ app()->getLocale() === 'ar' ? 'انتظار البيانات' : 'Awaiting parameters' }}
            </span>
        @endif
        <span>
            {{ $theme['label'] }}
        </span>
    </div>
</div>
