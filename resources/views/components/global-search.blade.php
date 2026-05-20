@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
@endphp

<!-- Global Search Modal Backdrop Overlay -->
<div 
    id="global-search-modal" 
    class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm p-4 sm:p-6 md:p-20 flex justify-center items-start transition-all duration-300"
    role="dialog"
    aria-modal="true"
    aria-labelledby="search-modal-title"
>
    <!-- Modal Container -->
    <div 
        id="global-search-container"
        class="w-full max-w-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-premium-2xl transform opacity-0 scale-95 transition-all duration-300 overflow-hidden flex flex-col mt-4 sm:mt-8"
        onclick="event.stopPropagation()"
    >
        <!-- Search Input Header -->
        <div class="relative flex items-center border-b border-slate-100 dark:border-slate-800/80 px-4 py-4 shrink-0">
            <svg class="absolute left-4 rtl:left-auto rtl:right-4 w-5 h-5 text-slate-400 dark:text-slate-500 shrink-0 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            
            <input 
                type="text" 
                id="global-search-input"
                class="w-full pl-8 pr-12 rtl:pl-12 rtl:pr-8 bg-transparent text-sm sm:text-base text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none border-none py-1 leading-relaxed"
                placeholder="{{ $isAr ? 'ابحث عن الموظفين، المشاريع، الفواتير، العملاء، أو المستندات...' : 'Search employees, projects, invoices, customers, documents...' }}"
                autocomplete="off"
                oninput="debouncedSearch(this.value)"
            >

            <!-- Close button -->
            <button 
                type="button" 
                onclick="closeGlobalSearchModal()"
                class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 focus:outline-none transition-colors"
                aria-label="{{ $isAr ? 'إغلاق' : 'Close' }}"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Search Results Panel -->
        <div 
            id="global-search-results" 
            class="max-h-[380px] sm:max-h-[460px] overflow-y-auto p-3 divide-y divide-slate-100/60 dark:divide-slate-800/40"
        >
            <!-- Default Welcome State -->
            <div id="search-default-state" class="py-12 text-center flex flex-col items-center justify-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-950/50 flex items-center justify-center text-slate-400 border border-slate-100 dark:border-slate-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300">
                        {{ $isAr ? 'البحث الذكي الشامل' : 'Unified Enterprise Search' }}
                    </h4>
                    <p class="text-[10px] sm:text-xs text-slate-400 mt-1 max-w-[280px] mx-auto leading-relaxed">
                        {{ $isAr ? 'ابدأ بكتابة أي كلمة للبحث الفوري في كافة الأقسام الرئيسية للنظام.' : 'Type any query to scan all system records including billing and projects instantly.' }}
                    </p>
                </div>
            </div>

            <!-- Loading Shimmer State (Hidden by default) -->
            <div id="search-loading-state" class="hidden space-y-5 py-4 px-2">
                @for($i = 0; $i < 3; $i++)
                    <div class="space-y-2 animate-pulse">
                        <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-20"></div>
                        <div class="space-y-1">
                            <div class="h-9 bg-slate-100 dark:bg-slate-800/50 rounded-xl"></div>
                            <div class="h-9 bg-slate-100 dark:bg-slate-800/50 rounded-xl w-5/6"></div>
                        </div>
                    </div>
                @endfor
            </div>

            <!-- No Results Found State (Hidden by default) -->
            <div id="search-empty-state" class="hidden py-12 text-center flex flex-col items-center justify-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-rose-50 dark:bg-rose-950/20 flex items-center justify-center text-rose-500 border border-rose-100 dark:border-rose-900/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300">
                        {{ $isAr ? 'لا توجد نتائج مطابقة' : 'No matches found' }}
                    </h4>
                    <p class="text-[10px] sm:text-xs text-slate-400 mt-1">
                        {{ $isAr ? 'يرجى التحقق من صياغة البحث أو استخدام كلمات بديلة.' : 'Try adjusting your search terms or keywords.' }}
                    </p>
                </div>
            </div>

            <!-- Grouped Dynamic Results container -->
            <div id="search-dynamic-results" class="hidden space-y-6 py-2">
                <!-- Group templates will be injected here via JavaScript -->
            </div>
        </div>

        <!-- Footer / Keyboard shortcuts guide -->
        <div class="px-4 py-2.5 border-t border-slate-100 dark:border-slate-800/70 bg-slate-50/50 dark:bg-slate-950/30 flex items-center justify-between shrink-0 text-[10px] text-slate-400">
            <div class="flex items-center gap-3">
                <span class="flex items-center gap-1">
                    <kbd class="px-1.5 py-0.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded shadow-sm font-bold">ESC</kbd>
                    <span>{{ $isAr ? 'للإغلاق' : 'to close' }}</span>
                </span>
                <span class="flex items-center gap-1 hidden sm:flex">
                    <kbd class="px-1.5 py-0.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded shadow-sm font-bold">↵</kbd>
                    <span>{{ $isAr ? 'للاختيار' : 'to select' }}</span>
                </span>
            </div>
            <div>
                <span>{{ $isAr ? 'نظام البحث السريع لشركة نوات' : 'Nawwat Fast Unified Search' }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Modal Control Scripts and API Integration -->
<script>
    let searchTimeout = null;
    const searchModal = document.getElementById('global-search-modal');
    const searchContainer = document.getElementById('global-search-container');
    const searchInput = document.getElementById('global-search-input');
    
    const defaultState = document.getElementById('search-default-state');
    const loadingState = document.getElementById('search-loading-state');
    const emptyState = document.getElementById('search-empty-state');
    const dynamicResults = document.getElementById('search-dynamic-results');

    // Close on backdrop click
    if (searchModal) {
        searchModal.addEventListener('click', function() {
            closeGlobalSearchModal();
        });
    }

    // Keyboard Shortcuts (Ctrl + K or Cmd + K)
    document.addEventListener('keydown', function(event) {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            openGlobalSearchModal();
        }
        
        if (event.key === 'Escape') {
            closeGlobalSearchModal();
        }
    });

    function openGlobalSearchModal() {
        if (!searchModal) return;
        searchModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        
        setTimeout(() => {
            searchContainer.classList.remove('opacity-0', 'scale-95');
            searchContainer.classList.add('opacity-100', 'scale-100');
            searchInput.focus();
        }, 10);
    }

    function closeGlobalSearchModal() {
        if (!searchModal) return;
        searchContainer.classList.remove('opacity-100', 'scale-100');
        searchContainer.classList.add('opacity-0', 'scale-95');
        
        setTimeout(() => {
            searchModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }, 150);
    }

    function debouncedSearch(value) {
        clearTimeout(searchTimeout);
        
        if (value.trim().length === 0) {
            showState('default');
            return;
        }

        if (value.trim().length < 2) {
            return; // Don't trigger search for single characters
        }

        showState('loading');

        searchTimeout = setTimeout(() => {
            performSearch(value.trim());
        }, 250);
    }

    function showState(state) {
        defaultState.classList.add('hidden');
        loadingState.classList.add('hidden');
        emptyState.classList.add('hidden');
        dynamicResults.classList.add('hidden');

        if (state === 'default') {
            defaultState.classList.remove('hidden');
        } else if (state === 'loading') {
            loadingState.classList.remove('hidden');
        } else if (state === 'empty') {
            emptyState.classList.remove('hidden');
        } else if (state === 'results') {
            dynamicResults.classList.remove('hidden');
        }
    }

    function performSearch(query) {
        fetch(`/global-search?q=${encodeURIComponent(query)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Search failed');
            return response.json();
        })
        .then(data => {
            renderResults(data);
        })
        .catch(error => {
            console.error('Global search error:', error);
            showState('empty');
        });
    }

    function renderResults(data) {
        dynamicResults.innerHTML = '';
        
        // Group configuration
        const groups = [
            { key: 'employees', titleAr: 'الموظفون', titleEn: 'Employees', icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>` },
            { key: 'projects', titleAr: 'المشاريع', titleEn: 'Projects', icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>` },
            { key: 'invoices', titleAr: 'الفواتير', titleEn: 'Invoices', icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>` },
            { key: 'customers', titleAr: 'العملاء', titleEn: 'Customers', icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>` },
            { key: 'documents', titleAr: 'المستندات', titleEn: 'Documents', icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>` }
        ];

        let hasAnyResults = false;
        const isAr = "{{ $isAr }}" === "1";

        groups.forEach(group => {
            const items = data[group.key] || [];
            if (items.length > 0) {
                hasAnyResults = true;
                
                // Create Group Header element
                const groupHeader = document.createElement('div');
                groupHeader.className = "flex items-center gap-2 px-3 py-2 text-[10px] sm:text-xs font-bold text-slate-400/90 dark:text-slate-500 uppercase tracking-wider select-none";
                groupHeader.innerHTML = `${group.icon} <span>${isAr ? group.titleAr : group.titleEn}</span>`;
                dynamicResults.appendChild(groupHeader);

                // Create List element
                const listContainer = document.createElement('div');
                listContainer.className = "space-y-1.5 mt-1 pb-4";
                
                items.forEach(item => {
                    const row = document.createElement('a');
                    row.href = item.url;
                    row.className = "flex items-center justify-between px-3.5 py-2.5 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/35 border border-transparent hover:border-slate-100 dark:hover:border-slate-800/60 transition-all duration-150 group";
                    
                    row.innerHTML = `
                        <div class="flex flex-col min-w-0 pr-4 rtl:pr-0 rtl:pl-4">
                            <span class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors truncate">
                                ${item.title}
                            </span>
                            ${item.subtitle ? `
                                <span class="text-[10px] sm:text-xs text-slate-400 dark:text-slate-500 mt-0.5 truncate font-medium">
                                    ${item.subtitle}
                                </span>
                            ` : ''}
                        </div>
                        <div class="shrink-0 flex items-center">
                            <svg class="w-4 h-4 text-slate-300 dark:text-slate-600 group-hover:text-brand-500 transition-colors transform group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    `;
                    
                    listContainer.appendChild(row);
                });
                
                dynamicResults.appendChild(listContainer);
            }
        });

        if (hasAnyResults) {
            showState('results');
        } else {
            showState('empty');
        }
    }
</script>
