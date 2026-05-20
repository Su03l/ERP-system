<x-app-layout>
    <!-- Dynamic Header -->
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                    {{ app()->getLocale() === 'ar' ? 'لوحة التحكم والمؤشرات' : 'Dashboard & Analytics' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ app()->getLocale() === 'ar' ? 'متابعة أداء الأقسام والعمليات التشغيلية والمالية مباشرة.' : 'Monitor department performance, operations, and financials in real-time.' }}
                </p>
            </div>
        </div>
    </x-slot>

    <!-- Slot for actions (Quick Actions) -->
    <x-slot name="actions">
        <button onclick="window.print()" class="btn-secondary">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            <span>{{ app()->getLocale() === 'ar' ? 'طباعة التقرير' : 'Print Report' }}</span>
        </button>
    </x-slot>

    <!-- Interactive Date Filter Panel -->
    <div class="erp-card p-5 bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800/80">
        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col lg:flex-row items-stretch lg:items-end gap-4">
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-2 uppercase tracking-wide">
                    {{ app()->getLocale() === 'ar' ? 'من تاريخ' : 'Date From' }}
                </label>
                <div class="relative">
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="erp-input w-full pl-3 pr-10 focus:ring-2 focus:ring-brand-500/20">
                </div>
            </div>
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-2 uppercase tracking-wide">
                    {{ app()->getLocale() === 'ar' ? 'إلى تاريخ' : 'Date To' }}
                </label>
                <div class="relative">
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="erp-input w-full pl-3 pr-10 focus:ring-2 focus:ring-brand-500/20">
                </div>
            </div>
            <div class="flex gap-3 shrink-0">
                <button type="submit" class="btn-primary px-6 py-2.5 font-semibold text-sm shadow-md shadow-brand-500/10 hover:shadow-brand-500/20 active:scale-98 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'تحديث الفلترة' : 'Update Filter' }}</span>
                </button>
                <a href="{{ route('dashboard') }}" class="btn-secondary px-5 py-2.5 text-sm hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors">
                    {{ app()->getLocale() === 'ar' ? 'إعادة تعيين' : 'Reset' }}
                </a>
            </div>
        </form>
    </div>

    <!-- Widgets Container -->
    @if($widgets->isEmpty())
        <!-- Empty State Dashboard -->
        <div class="erp-card p-12 text-center border-2 border-dashed border-slate-200 dark:border-slate-800 flex flex-col items-center justify-center space-y-4">
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-900 text-slate-400 dark:text-slate-600 flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">
                    {{ app()->getLocale() === 'ar' ? 'لوحة تحكم خالية' : 'Empty Dashboard' }}
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-md mx-auto">
                    {{ app()->getLocale() === 'ar' ? 'لا توجد أي مؤشرات أو بطاقات مضافة للمؤسسة حالياً. يرجى تهيئة إعدادات لوحة التحكم.' : 'No active widgets have been configured for your company yet.' }}
                </p>
            </div>
        </div>
    @else
        <!-- Widget Grid Layout (Dynamic 4-column responsive setup) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @foreach($widgets as $widget)
                @php
                    $sizeClass = match($widget->default_size) {
                        'small' => 'col-span-1',
                        'medium' => 'col-span-1 md:col-span-2',
                        'large' => 'col-span-1 md:col-span-3',
                        'full' => 'col-span-1 md:col-span-4',
                        default => 'col-span-1 md:col-span-2'
                    };

                    $widgetData = $resolvedData[$widget->id] ?? null;
                    
                    // Curated harmonic module colors
                    $moduleStyles = match($widget->module) {
                        'hr' => [
                            'bg' => 'bg-teal-50 dark:bg-teal-950/20',
                            'text' => 'text-teal-600 dark:text-teal-400',
                            'hover' => 'hover:border-teal-500/20 dark:hover:border-teal-500/10',
                            'badge' => 'bg-teal-500/10 text-teal-700 dark:text-teal-400',
                            'label' => app()->getLocale() === 'ar' ? 'الموارد البشرية' : 'HR',
                        ],
                        'attendance' => [
                            'bg' => 'bg-indigo-50 dark:bg-indigo-950/20',
                            'text' => 'text-indigo-600 dark:text-indigo-400',
                            'hover' => 'hover:border-indigo-500/20 dark:hover:border-indigo-500/10',
                            'badge' => 'bg-indigo-500/10 text-indigo-700 dark:text-indigo-400',
                            'label' => app()->getLocale() === 'ar' ? 'التحضير' : 'Attendance',
                        ],
                        'leave' => [
                            'bg' => 'bg-amber-50 dark:bg-amber-950/20',
                            'text' => 'text-amber-600 dark:text-amber-400',
                            'hover' => 'hover:border-amber-500/20 dark:hover:border-amber-500/10',
                            'badge' => 'bg-amber-500/10 text-amber-700 dark:text-amber-400',
                            'label' => app()->getLocale() === 'ar' ? 'الإجازات' : 'Leaves',
                        ],
                        'payroll' => [
                            'bg' => 'bg-violet-50 dark:bg-violet-950/20',
                            'text' => 'text-violet-600 dark:text-violet-400',
                            'hover' => 'hover:border-violet-500/20 dark:hover:border-violet-500/10',
                            'badge' => 'bg-violet-500/10 text-violet-700 dark:text-violet-400',
                            'label' => app()->getLocale() === 'ar' ? 'الرواتب' : 'Payroll',
                        ],
                        'accounting' => [
                            'bg' => 'bg-cyan-50 dark:bg-cyan-950/20',
                            'text' => 'text-cyan-600 dark:text-cyan-400',
                            'hover' => 'hover:border-cyan-500/20 dark:hover:border-cyan-500/10',
                            'badge' => 'bg-cyan-500/10 text-cyan-700 dark:text-cyan-400',
                            'label' => app()->getLocale() === 'ar' ? 'الحسابات' : 'Accounting',
                        ],
                        'projects' => [
                            'bg' => 'bg-rose-50 dark:bg-rose-950/20',
                            'text' => 'text-rose-600 dark:text-rose-400',
                            'hover' => 'hover:border-rose-500/20 dark:hover:border-rose-500/10',
                            'badge' => 'bg-rose-500/10 text-rose-700 dark:text-rose-400',
                            'label' => app()->getLocale() === 'ar' ? 'المشاريع' : 'Projects',
                        ],
                        'saas' => [
                            'bg' => 'bg-sky-50 dark:bg-sky-950/20',
                            'text' => 'text-sky-600 dark:text-sky-400',
                            'hover' => 'hover:border-sky-500/20 dark:hover:border-sky-500/10',
                            'badge' => 'bg-sky-500/10 text-sky-700 dark:text-sky-400',
                            'label' => app()->getLocale() === 'ar' ? 'النظام' : 'System',
                        ],
                        default => [
                            'bg' => 'bg-slate-50 dark:bg-slate-950/20',
                            'text' => 'text-slate-600 dark:text-slate-400',
                            'hover' => 'hover:border-slate-500/20 dark:hover:border-slate-500/10',
                            'badge' => 'bg-slate-500/10 text-slate-700 dark:text-slate-400',
                            'label' => app()->getLocale() === 'ar' ? 'عام' : 'General',
                        ],
                    };
                @endphp

                @if($widget->type === 'kpi')
                    <!-- 1. KPI WIDGET LAYOUT VIA REUSABLE COMPONENT -->
                    <x-kpi-card 
                        class="{{ $sizeClass }}"
                        :title="app()->getLocale() === 'ar' ? $widget->title_ar : $widget->title_en"
                        :value="$widgetData?->value"
                        :formatted-value="$widgetData?->formattedValue"
                        :trend="$widgetData?->trend"
                        :comparison-value="$widgetData ? (($widgetData->trend === 'up' ? '+' : '') . ($widgetData->comparisonValue ?? '0') . '%') : null"
                        :module="$widget->module"
                    />
                @else
                    <!-- Dynamic Widget Shell for charts and tables -->
                    <div class="erp-card p-6 flex flex-col justify-between overflow-hidden {{ $sizeClass }} border border-slate-200/50 dark:border-slate-800/60 {{ $moduleStyles['hover'] }}">
                        
                        @if($widget->type === 'chart')
                            <!-- 2. CHART WIDGET LAYOUT -->
                            <div class="space-y-4 flex-1 flex flex-col justify-between">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-slate-800 dark:text-white text-sm">
                                            {{ app()->getLocale() === 'ar' ? $widget->title_ar : $widget->title_en }}
                                        </h4>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold {{ $moduleStyles['badge'] }}">
                                            {{ $moduleStyles['label'] }}
                                        </span>
                                    </div>
                                </div>

                                @php
                                    $chartValues = $widgetData->metadata['values'] ?? [];
                                    $totalSum = collect($chartValues)->sum('value') ?: 0;
                                    $maxVal = collect($chartValues)->max('value') ?: 1;
                                @endphp

                                @if(empty($chartValues))
                                    <!-- Chart Empty State -->
                                    <div class="flex-1 flex flex-col items-center justify-center p-6 text-center">
                                        <svg class="w-8 h-8 text-slate-300 dark:text-slate-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.003 9.003 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                                        <span class="text-xs text-slate-400">{{ app()->getLocale() === 'ar' ? 'لا توجد بيانات للرسم البياني' : 'No chart data available' }}</span>
                                    </div>
                                @else
                                    <!-- Dynamic Horizontal Bar Chart -->
                                    <div class="space-y-3.5 flex-1 py-1">
                                        @foreach($chartValues as $row)
                                            @php
                                                $percentage = $totalSum > 0 ? round(($row['value'] / $totalSum) * 100, 1) : 0;
                                                $widthPercent = round(($row['value'] / $maxVal) * 100);
                                            @endphp
                                            <div class="space-y-1">
                                                <div class="flex justify-between text-xs font-semibold">
                                                    <span class="text-slate-700 dark:text-slate-300">{{ $row['label'] }}</span>
                                                    <span class="text-slate-500 dark:text-slate-400">
                                                        {{ $row['value'] }} ({{ $percentage }}%)
                                                    </span>
                                                </div>
                                                <!-- Ambient Progress Bar -->
                                                <div class="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                                    <div class="h-full rounded-full transition-all duration-500 ease-out bg-gradient-to-r from-brand-500 to-teal-400 dark:from-brand-600 dark:to-teal-500" 
                                                         style="width: {{ $widthPercent }}%">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[10px] text-slate-400 font-semibold uppercase tracking-wider">
                                    <span>{{ app()->getLocale() === 'ar' ? 'إجمالي المدخلات' : 'Total Inputs' }}: {{ $totalSum }}</span>
                                    <span>{{ $moduleStyles['label'] }}</span>
                                </div>
                            </div>

                        @elseif($widget->type === 'table')
                            <!-- 3. TABLE WIDGET LAYOUT -->
                            <div class="space-y-4 flex-1 flex flex-col justify-between">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-slate-800 dark:text-white text-sm">
                                            {{ app()->getLocale() === 'ar' ? $widget->title_ar : $widget->title_en }}
                                        </h4>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold {{ $moduleStyles['badge'] }}">
                                            {{ $moduleStyles['label'] }}
                                        </span>
                                    </div>
                                </div>

                                @php
                                    $tableRows = $widgetData->metadata['values'] ?? [];
                                @endphp

                                @if(empty($tableRows))
                                    <!-- Table Empty State -->
                                    <div class="flex-1 flex flex-col items-center justify-center p-6 text-center">
                                        <svg class="w-8 h-8 text-slate-300 dark:text-slate-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        <span class="text-xs text-slate-400">{{ app()->getLocale() === 'ar' ? 'لا توجد بيانات للجدول' : 'No tabular data available' }}</span>
                                    </div>
                                @else
                                    <!-- Interactive ERP Inner Table -->
                                    <div class="erp-table-container max-h-[220px] overflow-y-auto">
                                        <table class="erp-table">
                                            <thead>
                                                <tr>
                                                    <th class="py-2.5 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                                        {{ app()->getLocale() === 'ar' ? 'التفصيل' : 'Label' }}
                                                    </th>
                                                    <th class="py-2.5 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center">
                                                        {{ app()->getLocale() === 'ar' ? 'العدد' : 'Count' }}
                                                    </th>
                                                    <th class="py-2.5 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-right">
                                                        {{ app()->getLocale() === 'ar' ? 'التكلفة الإجمالية' : 'Total Amount' }}
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($tableRows as $row)
                                                    <tr>
                                                        <td class="py-2 px-3 text-xs font-semibold text-slate-800 dark:text-white">
                                                            {{ $row['label'] }}
                                                        </td>
                                                        <td class="py-2 px-3 text-xs text-center font-medium text-slate-500">
                                                            {{ $row['employee_count'] ?? $row['value'] ?? '—' }}
                                                        </td>
                                                        <td class="py-2 px-3 text-xs text-right font-black text-teal-600 dark:text-teal-400">
                                                            @if(isset($row['total_payroll_cost']))
                                                                {{ app()->getLocale() === 'ar' ? number_format($row['total_payroll_cost'], 2) . ' ر.س' : 'SAR ' . number_format($row['total_payroll_cost'], 2) }}
                                                            @else
                                                                —
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[10px] text-slate-400 font-semibold uppercase tracking-wider">
                                    <span>{{ app()->getLocale() === 'ar' ? 'مسيرة الرواتب النشطة' : 'Active payroll tracks' }}</span>
                                    <span>{{ $moduleStyles['label'] }}</span>
                                </div>
                            </div>
                        @endif

                    </div>
                @endif
            @endforeach
        </div>
    @endif
</x-app-layout>
