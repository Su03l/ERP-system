@props([
    'type' => 'list', // 'table', 'cards', 'list', 'profile'
    'rows' => 3,
    'cols' => 4,
])

<div class="animate-pulse space-y-4 w-full">
    @if($type === 'table')
        <!-- Table skeleton structure -->
        <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden bg-white dark:bg-slate-900">
            <div class="bg-slate-50 dark:bg-slate-800 h-10 flex items-center px-4 gap-4 border-b border-slate-200 dark:border-slate-700">
                @for($i = 0; $i < $cols; $i++)
                    <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded-md w-1/4"></div>
                @endfor
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @for($r = 0; $r < $rows; $r++)
                    <div class="p-4 flex items-center gap-4">
                        @for($c = 0; $c < $cols; $c++)
                            <div class="h-3 bg-slate-100 dark:bg-slate-800 rounded-md w-1/4 {{ $c === 0 ? 'w-1/3 h-4 bg-slate-200 dark:bg-slate-700' : '' }}"></div>
                        @endfor
                    </div>
                @endfor
            </div>
        </div>

    @elseif($type === 'cards')
        <!-- Card Grid skeleton structure -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @for($r = 0; $r < $rows; $r++)
                <div class="erp-card p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-slate-200 dark:bg-slate-800"></div>
                        <div class="space-y-2 flex-1">
                            <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded-md w-2/3"></div>
                            <div class="h-3 bg-slate-100 dark:bg-slate-800 rounded-md w-1/3"></div>
                        </div>
                    </div>
                    <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <div class="h-3 bg-slate-100 dark:bg-slate-800 rounded-md w-full"></div>
                        <div class="h-3 bg-slate-100 dark:bg-slate-800 rounded-md w-5/6"></div>
                    </div>
                </div>
            @endfor
        </div>

    @elseif($type === 'profile')
        <!-- Profile details skeleton structure -->
        <div class="erp-card p-8 flex flex-col md:flex-row gap-6">
            <div class="w-24 h-24 rounded-full bg-slate-200 dark:bg-slate-800 shrink-0 mx-auto md:mx-0"></div>
            <div class="space-y-4 flex-1">
                <div class="space-y-2">
                    <div class="h-5 bg-slate-200 dark:bg-slate-700 rounded-md w-1/3"></div>
                    <div class="h-3 bg-slate-100 dark:bg-slate-800 rounded-md w-1/4"></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div class="space-y-2">
                        <div class="h-3 bg-slate-100 dark:bg-slate-800 rounded-md w-1/3"></div>
                        <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded-md w-2/3"></div>
                    </div>
                    <div class="space-y-2">
                        <div class="h-3 bg-slate-100 dark:bg-slate-800 rounded-md w-1/3"></div>
                        <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded-md w-2/3"></div>
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- List rows skeleton structure -->
        <div class="space-y-3">
            @for($r = 0; $r < $rows; $r++)
                <div class="p-4 erp-card flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 flex-1">
                        <div class="w-8 h-8 rounded-lg bg-slate-200 dark:bg-slate-800 shrink-0"></div>
                        <div class="space-y-2 flex-1">
                            <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded-md w-1/3"></div>
                            <div class="h-3 bg-slate-100 dark:bg-slate-800 rounded-md w-2/3"></div>
                        </div>
                    </div>
                    <div class="h-8 bg-slate-200 dark:bg-slate-800 rounded-md w-20 shrink-0"></div>
                </div>
            @endfor
        </div>
    @endif
</div>
