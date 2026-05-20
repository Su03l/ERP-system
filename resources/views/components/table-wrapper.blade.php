@props([
    'headers' => [], // Array like: ['name' => ['label' => 'Name', 'sortable' => true, 'align' => 'left']]
    'rows' => null, // Paginated collection or array
    'loading' => false,
    'empty' => false,
    'search' => true,
    'searchQuery' => '',
    'searchPlaceholder' => null,
    'sortField' => null,
    'sortDirection' => 'asc',
    'bulkActions' => true,
    'exportActions' => true,
    'title' => null,
    'subtitle' => null,
])

@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    
    // Auto-calculate pagination details if $rows is standard paginator
    $hasPaginator = $rows instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    
    $totalCount = $hasPaginator ? $rows->total() : (is_array($rows) ? count($rows) : ($rows?->count() ?? 0));
    $perPage = $hasPaginator ? $rows->perPage() : $totalCount;
    $currentPage = $hasPaginator ? $rows->currentPage() : 1;
    
    $from = $totalCount > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
    $to = min($currentPage * $perPage, $totalCount);

    $defaultSearchPlaceholder = $isAr ? 'البحث في الجدول...' : 'Search table...';
    $placeholderText = $searchPlaceholder ?? $defaultSearchPlaceholder;
@endphp

<div class="space-y-4">
    <!-- Top Control Bar (Search, Filters, Export) -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200/60 dark:border-slate-800/80 shadow-premium">
        
        <!-- Left: Search and Title -->
        <div class="flex-1 flex flex-col sm:flex-row sm:items-center gap-3">
            @if($title)
                <div class="shrink-0 pr-4 border-r border-slate-100 dark:border-slate-800 rtl:pr-0 rtl:pl-4 rtl:border-r-0 rtl:border-l">
                    <h3 class="font-bold text-slate-800 dark:text-white text-base leading-tight">{{ $title }}</h3>
                    @if($subtitle)
                        <p class="text-xs text-slate-400 mt-0.5">{{ $subtitle }}</p>
                    @endif
                </div>
            @endif

            @if($search)
                <div class="relative flex-1 max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 dark:text-slate-500 rtl:left-auto rtl:right-0 rtl:pr-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $searchQuery }}" 
                        placeholder="{{ $placeholderText }}" 
                        class="erp-input w-full pl-9 pr-4 rtl:pl-4 rtl:pr-9 text-xs py-2 h-9 focus:ring-brand-500/20"
                        oninput="this.dispatchEvent(new CustomEvent('table-search', { bubbles: true, detail: { query: this.value } }))"
                    >
                </div>
            @endif
        </div>

        <!-- Right: Filters and Exports -->
        <div class="flex items-center justify-end gap-3 shrink-0">
            <!-- Custom Filter Slot -->
            @if(isset($filters))
                <div class="relative inline-block text-left" id="table-filter-dropdown">
                    <button type="button" onclick="document.getElementById('table-filter-content').classList.toggle('hidden')" class="btn-secondary text-xs px-3 py-2 h-9 gap-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        <span>{{ $isAr ? 'تصفية إضافية' : 'Filters' }}</span>
                    </button>
                    <!-- Filter Content Dropdown -->
                    <div id="table-filter-content" class="hidden absolute right-0 rtl:right-auto rtl:left-0 mt-2 w-72 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-premium-lg z-50 p-4 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-xs font-bold text-slate-800 dark:text-white">{{ $isAr ? 'خيارات الفلترة' : 'Filter Options' }}</span>
                            <button type="button" onclick="document.getElementById('table-filter-content').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        {{ $filters }}
                    </div>
                </div>
            @endif

            <!-- Export Buttons Panel -->
            @if($exportActions)
                <div class="relative" id="table-export-dropdown">
                    <button type="button" onclick="document.getElementById('table-export-content').classList.toggle('hidden')" class="btn-secondary text-xs px-3 py-2 h-9 gap-1.5">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        <span>{{ $isAr ? 'تصدير البيانات' : 'Export' }}</span>
                    </button>
                    <!-- Export Options dropdown -->
                    <div id="table-export-content" class="hidden absolute right-0 rtl:right-auto rtl:left-0 mt-2 w-44 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-premium-lg z-40 py-1.5">
                        <button type="button" class="w-full flex items-center gap-2 px-4 py-2 text-xs text-left rtl:text-right text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Excel (.xlsx)</span>
                        </button>
                        <button type="button" class="w-full flex items-center gap-2 px-4 py-2 text-xs text-left rtl:text-right text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                            <span>CSV (.csv)</span>
                        </button>
                        <button type="button" class="w-full flex items-center gap-2 px-4 py-2 text-xs text-left rtl:text-right text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span>PDF (.pdf)</span>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Bulk Actions Interactive Panel (Appears when rows are selected) -->
    @if($bulkActions)
        <div id="bulk-actions-panel" class="hidden flex flex-row items-center justify-between bg-brand-50 dark:bg-brand-950/20 border border-brand-200 dark:border-brand-900/50 p-3.5 rounded-xl transition-all shadow-glass animate-pulse-once">
            <div class="flex items-center gap-3">
                <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                <span class="text-xs font-bold text-brand-900 dark:text-brand-400">
                    <span id="selected-count">0</span> {{ $isAr ? 'عناصر تم اختيارها' : 'items selected' }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="text-xs font-semibold px-3 py-1.5 bg-brand-500 text-white rounded-lg hover:bg-brand-600 transition-colors">
                    {{ $isAr ? 'تعديل جماعي' : 'Bulk Edit' }}
                </button>
                <button type="button" class="text-xs font-semibold px-3 py-1.5 bg-rose-600 text-white rounded-lg hover:bg-rose-700 transition-colors">
                    {{ $isAr ? 'حذف المحدد' : 'Delete Selected' }}
                </button>
                <button type="button" onclick="deselectAllTableRows()" class="text-xs font-semibold px-3 py-1.5 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                    {{ $isAr ? 'إلغاء التحديد' : 'Cancel' }}
                </button>
            </div>
        </div>
    @endif

    <!-- Main Table Container -->
    <div class="erp-table-container bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800/80 shadow-premium overflow-hidden">
        <div class="overflow-x-auto w-full relative">
            <table class="erp-table w-full text-left rtl:text-right border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800">
                        <!-- Bulk Select Header Checkbox -->
                        @if($bulkActions)
                            <th class="w-10 px-4 py-3 bg-slate-50 dark:bg-slate-900 text-center">
                                <input 
                                    type="checkbox" 
                                    id="select-all-checkbox"
                                    onclick="toggleAllTableRows(this)"
                                    class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-slate-300 dark:border-slate-700 dark:bg-slate-950 cursor-pointer"
                                >
                            </th>
                        @endif

                        <!-- Main Table Headers -->
                        @foreach($headers as $key => $column)
                            @php
                                $align = $column['align'] ?? 'start';
                                $alignClass = match($align) {
                                    'center' => 'text-center',
                                    'end' => 'text-end',
                                    default => 'text-start'
                                };
                                $sortable = $column['sortable'] ?? false;
                                $isSorted = $sortField === $key;
                            @endphp
                            <th class="px-4 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900 {{ $alignClass }}">
                                @if($sortable)
                                    <button 
                                        type="button" 
                                        onclick="sortTableColumn('{{ $key }}', '{{ $isSorted && $sortDirection === 'asc' ? 'desc' : 'asc' }}')"
                                        class="inline-flex items-center gap-1.5 hover:text-slate-800 dark:hover:text-white transition-colors cursor-pointer group focus:outline-none"
                                    >
                                        <span>{{ $column['label'] }}</span>
                                        <span class="text-[10px] text-slate-300 dark:text-slate-600 group-hover:text-slate-500 flex flex-col leading-none">
                                            @if($isSorted)
                                                @if($sortDirection === 'asc')
                                                    <svg class="w-3 h-3 text-brand-500 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 15l7-7 7 7"></path></svg>
                                                @else
                                                    <svg class="w-3 h-3 text-brand-500 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M19 9l-7 7-7-7"></path></svg>
                                                @endif
                                            @else
                                                <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                                            @endif
                                        </span>
                                    </button>
                                @else
                                    <span>{{ $column['label'] }}</span>
                                @endif
                            </th>
                        @endforeach

                        <!-- Action Column Header -->
                        @if(isset($rowActionsSlot) || isset($body))
                            <th class="px-4 py-3.5 text-xs font-bold text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900 text-center w-24">
                                {{ $isAr ? 'الإجراءات' : 'Actions' }}
                            </th>
                        @endif
                    </tr>
                </thead>

                <!-- Loading State Shimmer Body -->
                @if($loading)
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @for($i = 0; $i < 5; $i++)
                            <tr class="animate-pulse bg-white dark:bg-slate-900/50">
                                @if($bulkActions)
                                    <td class="p-4 text-center">
                                        <div class="w-4 h-4 bg-slate-200 dark:bg-slate-800 rounded mx-auto"></div>
                                    </td>
                                @endif
                                @foreach($headers as $column)
                                    <td class="p-4">
                                        <div class="h-3.5 bg-slate-200 dark:bg-slate-800 rounded w-2/3"></div>
                                    </td>
                                @endforeach
                                @if(isset($rowActionsSlot) || isset($body))
                                    <td class="p-4 text-center">
                                        <div class="h-6 bg-slate-200 dark:bg-slate-800 rounded-lg w-12 mx-auto"></div>
                                    </td>
                                @endif
                            </tr>
                        @endfor
                    </tbody>

                <!-- Empty State Body -->
                @elseif($empty || $totalCount === 0)
                    <tbody>
                        <tr>
                            <td colspan="{{ count($headers) + ($bulkActions ? 1 : 0) + (isset($rowActionsSlot) || isset($body) ? 1 : 0) }}" class="p-0">
                                <div class="flex flex-col items-center justify-center p-12 text-center bg-white dark:bg-slate-900">
                                    <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-950 flex items-center justify-center mb-4 border border-slate-100 dark:border-slate-900">
                                        <svg class="w-8 h-8 text-slate-400 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <h4 class="text-sm font-bold text-slate-800 dark:text-white">
                                        {{ $isAr ? 'لم يتم العثور على أي نتائج' : 'No entries found' }}
                                    </h4>
                                    <p class="text-xs text-slate-400 mt-1 max-w-sm">
                                        {{ $isAr ? 'لا توجد بيانات مطابقة لمعايير البحث والفلترة حالياً.' : 'There are no active records matching the selected configurations right now.' }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>

                <!-- Dynamic Data Body -->
                @else
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                        @if(isset($body))
                            {{ $body }}
                        @else
                            <!-- Default Loop Renderer (if no custom template is provided) -->
                            @foreach($rows as $index => $row)
                                @php
                                    $rowId = is_array($row) ? ($row['id'] ?? $index) : ($row->id ?? $index);
                                @endphp
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors duration-150 erp-row" data-row-id="{{ $rowId }}">
                                    <!-- Bulk Action Row Checkbox -->
                                    @if($bulkActions)
                                        <td class="px-4 py-3.5 text-center">
                                            <input 
                                                type="checkbox" 
                                                value="{{ $rowId }}"
                                                onclick="updateSelectedTableCount()"
                                                class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-slate-300 dark:border-slate-700 dark:bg-slate-950 cursor-pointer row-select-checkbox"
                                            >
                                        </td>
                                    @endif

                                    <!-- Render Cells -->
                                    @foreach($headers as $key => $column)
                                        @php
                                            $align = $column['align'] ?? 'start';
                                            $alignClass = match($align) {
                                                'center' => 'text-center',
                                                'end' => 'text-end',
                                                default => 'text-start'
                                            };
                                            
                                            // Handle array vs object access
                                            $val = is_array($row) ? ($row[$key] ?? '—') : ($row->{$key} ?? '—');
                                        @endphp
                                        <td class="px-4 py-3.5 text-xs text-slate-700 dark:text-slate-300 {{ $alignClass }}">
                                            @if($key === 'status')
                                                <!-- Automatic Badge Parser -->
                                                @php
                                                    $statusClean = strtolower($val);
                                                    $badgeClass = match($statusClean) {
                                                        'active', 'approved', 'paid', 'completed' => 'erp-badge-success',
                                                        'pending', 'draft', 'on-hold', 'sent' => 'erp-badge-warning',
                                                        'inactive', 'rejected', 'failed', 'overdue' => 'erp-badge-danger',
                                                        default => 'erp-badge-info'
                                                    };
                                                @endphp
                                                <span class="erp-badge {{ $badgeClass }} text-[10px] px-2 py-0.5 font-bold">
                                                    {{ $val }}
                                                </span>
                                            @else
                                                <span class="font-medium">{{ $val }}</span>
                                            @endif
                                        </td>
                                    @endforeach

                                    <!-- Actions Cell Dropdown Menu -->
                                    @if(isset($rowActionsSlot))
                                        <td class="px-4 py-3 text-center w-24">
                                            <div class="relative inline-block text-left" id="row-dropdown-container-{{ $rowId }}">
                                                <button 
                                                    type="button" 
                                                    onclick="toggleTableRowDropdown('{{ $rowId }}')" 
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path>
                                                    </svg>
                                                </button>
                                                <!-- Hover Action Floating Dropdown Menu -->
                                                <div id="row-actions-dropdown-{{ $rowId }}" class="hidden absolute right-0 rtl:right-auto rtl:left-0 mt-1 w-32 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-premium-lg z-50 py-1">
                                                    {{ $rowActionsSlot($row) }}
                                                </div>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                @endif
            </table>
        </div>
    </div>

    <!-- Bottom Pagination Control Bar -->
    @if(!$loading && $totalCount > 0)
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200/60 dark:border-slate-800/80 shadow-premium">
            <!-- Range Summary -->
            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                @if($isAr)
                    عرض <span class="font-bold text-slate-800 dark:text-white">{{ $from }}</span> إلى <span class="font-bold text-slate-800 dark:text-white">{{ $to }}</span> من أصل <span class="font-bold text-slate-800 dark:text-white">{{ $totalCount }}</span> سجل
                @else
                    Showing <span class="font-bold text-slate-800 dark:text-white">{{ $from }}</span> to <span class="font-bold text-slate-800 dark:text-white">{{ $to }}</span> of <span class="font-bold text-slate-800 dark:text-white">{{ $totalCount }}</span> entries
                @endif
            </div>

            <!-- Page Buttons -->
            <div>
                @if($hasPaginator)
                    <div class="text-xs">
                        {{ $rows->links() }}
                    </div>
                @else
                    <div class="flex items-center gap-1.5">
                        <button type="button" class="btn-secondary text-[11px] px-2.5 py-1.5 h-8 gap-1 opacity-50 cursor-not-allowed">
                            <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            <span>{{ $isAr ? 'السابق' : 'Previous' }}</span>
                        </button>
                        <button type="button" class="btn-primary text-[11px] px-3 py-1.5 h-8 bg-brand-500 text-white rounded-lg">1</button>
                        <button type="button" class="btn-secondary text-[11px] px-2.5 py-1.5 h-8 gap-1 opacity-50 cursor-not-allowed">
                            <span>{{ $isAr ? 'التالي' : 'Next' }}</span>
                            <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

<!-- Essential Dynamic Dropdowns and Bulk Checkbox Scripts -->
<script>
    // Toggle row action dropdown menus safely
    function toggleTableRowDropdown(rowId) {
        const dropdown = document.getElementById(`row-actions-dropdown-${rowId}`);
        if (!dropdown) return;
        
        // Hide all other open dropdowns first
        document.querySelectorAll('[id^="row-actions-dropdown-"]').forEach(item => {
            if (item.id !== `row-actions-dropdown-${rowId}`) {
                item.classList.add('hidden');
            }
        });
        
        dropdown.classList.toggle('hidden');
    }

    // Sort column handler
    function sortTableColumn(field, direction) {
        window.dispatchEvent(new CustomEvent('table-sort', {
            detail: { field: field, direction: direction }
        }));
        
        // Dynamic URL helper for static pages
        const url = new URL(window.location.href);
        url.searchParams.set('sort', field);
        url.searchParams.set('direction', direction);
        window.location.href = url.toString();
    }

    // Toggle all checkboxes
    function toggleAllTableRows(masterCheckbox) {
        const checkboxes = document.querySelectorAll('.row-select-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = masterCheckbox.checked;
        });
        updateSelectedTableCount();
    }

    // Update bulk counter bar
    function updateSelectedTableCount() {
        const checkboxes = document.querySelectorAll('.row-select-checkbox');
        const bulkPanel = document.getElementById('bulk-actions-panel');
        const selectedSpan = document.getElementById('selected-count');
        const masterCb = document.getElementById('select-all-checkbox');
        
        let checkedCount = 0;
        checkboxes.forEach(cb => {
            if (cb.checked) checkedCount++;
        });

        if (masterCb) {
            masterCb.checked = checkedCount === checkboxes.length && checkboxes.length > 0;
        }

        if (checkedCount > 0) {
            if (bulkPanel) bulkPanel.classList.remove('hidden');
            if (selectedSpan) selectedSpan.innerText = checkedCount;
        } else {
            if (bulkPanel) bulkPanel.classList.add('hidden');
        }
    }

    // Deselect all elements helper
    function deselectAllTableRows() {
        const checkboxes = document.querySelectorAll('.row-select-checkbox');
        const masterCb = document.getElementById('select-all-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = false;
        });
        if (masterCb) masterCb.checked = false;
        updateSelectedTableCount();
    }

    // Global click listener to close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        // Toggle exports
        const exportContainer = document.getElementById('table-export-dropdown');
        const exportContent = document.getElementById('table-export-content');
        if (exportContainer && exportContent && !exportContainer.contains(e.target)) {
            exportContent.classList.add('hidden');
        }

        // Toggle filters
        const filterContainer = document.getElementById('table-filter-dropdown');
        const filterContent = document.getElementById('table-filter-content');
        if (filterContainer && filterContent && !filterContainer.contains(e.target)) {
            filterContent.classList.add('hidden');
        }

        // Toggle row actions
        if (!e.target.closest('[id^="row-dropdown-container-"]')) {
            document.querySelectorAll('[id^="row-actions-dropdown-"]').forEach(item => {
                item.classList.add('hidden');
            });
        }
    });
</script>
