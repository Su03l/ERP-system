<header class="h-16 flex items-center justify-between px-4 md:px-8 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 shrink-0 select-none z-10">
    <!-- Left Hand: Breadcrumbs or Dynamic Tenant Context -->
    <div class="flex items-center gap-4">
        <!-- Sidebar Toggle Mobile (Button) -->
        <button class="md:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none" @click="mobileSidebarOpen = !mobileSidebarOpen">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>

        <!-- Company Context Badge Switcher Placeholder -->
        <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-3 py-1.5 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors">
            <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                {{ auth()->user()?->company?->name ?? 'الشركة الافتراضية' }}
            </span>
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </div>

        <!-- Global Search Bar Trigger (Desktop) -->
        <div class="relative hidden md:block w-60 lg:w-72">
            <button 
                type="button" 
                onclick="openGlobalSearchModal()"
                class="w-full flex items-center justify-between px-3 py-1.5 text-[11px] text-slate-400 bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800/80 rounded-lg hover:bg-slate-100/50 dark:hover:bg-slate-900 transition-colors focus:outline-none cursor-pointer"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'البحث عن أي شيء...' : 'Search anything...' }}</span>
                </div>
                <kbd class="hidden sm:inline-flex items-center gap-0.5 px-1.5 py-0.5 text-[9px] font-bold text-slate-400 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded shadow-sm">
                    <span>Ctrl</span>
                    <span>K</span>
                </kbd>
            </button>
        </div>
    </div>

    <!-- Right Hand: Localized utilities and user settings -->
    <div class="flex items-center gap-4">
        <!-- Mobile Search Trigger Button -->
        <button 
            type="button" 
            onclick="openGlobalSearchModal()"
            class="md:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none cursor-pointer"
            aria-label="{{ app()->getLocale() === 'ar' ? 'البحث' : 'Search' }}"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </button>

        <!-- Language Switcher Placeholder -->
        <div class="flex items-center">
            @if(app()->getLocale() === 'ar')
                <a href="?locale=en" class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white px-2.5 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <span>English</span>
                    <span class="text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-500 px-1 py-0.5 rounded">EN</span>
                </a>
            @else
                <a href="?locale=ar" class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white px-2.5 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <span class="font-arabic">العربية</span>
                    <span class="text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-500 px-1 py-0.5 rounded">AR</span>
                </a>
            @endif
        </div>

        <!-- Notification Center -->
        <x-notifications-dropdown />

        <div class="h-6 w-px bg-slate-200 dark:bg-slate-800"></div>

        <!-- User Dropdown & Meta details -->
        <div class="flex items-center gap-3 cursor-pointer group">
            <div class="flex flex-col text-end hidden sm:flex">
                <span class="text-xs font-semibold text-slate-900 dark:text-white group-hover:text-brand-500 transition-colors leading-none">
                    {{ auth()->user()?->name ?? 'Guest User' }}
                </span>
                <span class="text-[10px] text-slate-400 font-medium mt-1 leading-none">
                    {{ auth()->user()?->email ?? 'guest@nawwat.sa' }}
                </span>
            </div>
            <!-- Avatar container -->
            <div class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center font-bold text-slate-600 dark:text-slate-300 ring-0 group-hover:ring-2 group-hover:ring-brand-500 transition-all">
                {{ substr(auth()->user()?->name ?? 'U', 0, 2) }}
            </div>
        </div>
    </div>
</header>
