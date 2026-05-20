@props([
    'loading' => false,
])

@php
    $user = auth()->user();
    $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
    
    // Fetch top 5 recent notifications
    $notifications = $user ? $user->notifications()->take(5)->get() : collect();
    
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
@endphp

<div class="relative inline-block text-left" id="notifications-dropdown-container">
    <!-- Trigger Button -->
    <button 
        type="button" 
        onclick="toggleNotificationsDropdown()"
        class="relative p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none transition-colors cursor-pointer"
        aria-label="{{ $isAr ? 'التنبيهات' : 'Notifications' }}"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        
        @if($unreadCount > 0)
            <span class="absolute top-1 right-1 min-w-[16px] h-4 px-1 rounded-full bg-rose-600 text-white text-[9px] font-black flex items-center justify-center ring-2 ring-white dark:ring-slate-900 animate-bounce">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown Content Panel -->
    <div 
        id="notifications-dropdown-menu" 
        class="hidden absolute right-0 rtl:right-auto rtl:left-0 mt-2 w-80 sm:w-96 bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl shadow-premium-lg z-50 overflow-hidden"
    >
        <!-- Panel Header -->
        <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800/85 bg-slate-50/50 dark:bg-slate-950/30 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="font-bold text-xs sm:text-sm text-slate-800 dark:text-white">
                    {{ $isAr ? 'التنبيهات الإدارية' : 'System Notifications' }}
                </span>
                @if($unreadCount > 0)
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold bg-brand-500/10 text-brand-700 dark:text-brand-400">
                        {{ $unreadCount }} {{ $isAr ? 'جديد' : 'new' }}
                    </span>
                @endif
            </div>
            
            @if($unreadCount > 0)
                <form action="{{ route('notifications.mark-all-as-read') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-[10px] sm:text-xs font-bold text-brand-600 dark:text-brand-400 hover:text-brand-700 transition-colors focus:outline-none">
                        {{ $isAr ? 'تحديد الكل كمقروء' : 'Mark all read' }}
                    </button>
                </form>
            @endif
        </div>

        <!-- Notification List -->
        <div class="max-h-[320px] overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/60" id="notifications-list">
            
            @if($loading)
                <!-- Loading Shimmer Template -->
                @for($i = 0; $i < 3; $i++)
                    <div class="p-4 flex gap-3 animate-pulse bg-white dark:bg-slate-900">
                        <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-800 shrink-0"></div>
                        <div class="flex-1 space-y-2">
                            <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-3/4"></div>
                            <div class="h-2.5 bg-slate-200 dark:bg-slate-800 rounded w-1/2"></div>
                        </div>
                    </div>
                @endfor
                
            @elseif($notifications->isEmpty())
                <!-- Elegant Zero State -->
                <div class="py-12 px-4 text-center flex flex-col items-center justify-center space-y-3 bg-white dark:bg-slate-900">
                    <div class="w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-950 flex items-center justify-center text-slate-400 dark:text-slate-600 border border-slate-100 dark:border-slate-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300">
                            {{ $isAr ? 'لا توجد تنبيهات جديدة' : 'No new notifications' }}
                        </h4>
                        <p class="text-[10px] sm:text-xs text-slate-400 mt-1 max-w-[200px] mx-auto">
                            {{ $isAr ? 'ستظهر هنا آخر الإشعارات المتعلقة بحسابك أو نظام الأمان.' : 'You are all caught up! Recent security and billing updates will show up here.' }}
                        </p>
                    </div>
                </div>
                
            @else
                <!-- Active Notification Items -->
                @foreach($notifications as $item)
                    @php
                        $isUnread = is_null($item->read_at);
                        $typeStr = strtolower($item->type);
                        
                        // Dynamic configuration based on notification model payload
                        $config = match(true) {
                            str_contains($typeStr, 'security') => [
                                'bg' => 'bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400 border-amber-200/40',
                                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>',
                                'route' => Route::has('security-settings.index') ? route('security-settings.index') : '#',
                            ],
                            str_contains($typeStr, 'subscription') => [
                                'bg' => 'bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 border-indigo-200/40',
                                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>',
                                'route' => Route::has('company-subscriptions.index') ? route('company-subscriptions.index') : '#',
                            ],
                            str_contains($typeStr, 'document') => [
                                'bg' => 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 border-emerald-200/40',
                                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>',
                                'route' => '#',
                            ],
                            default => [
                                'bg' => 'bg-slate-50 dark:bg-slate-950 text-slate-500 border-slate-200/40',
                                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                                'route' => '#',
                            ]
                        };

                        // Localize database payloads
                        $messageText = '';
                        if (isset($item->data['message'])) {
                            $messageText = $item->data['message'];
                        } elseif (isset($item->data['event'])) {
                            $eventClean = str_replace('_', ' ', $item->data['event']);
                            $messageText = ucwords($eventClean);
                        } else {
                            $messageText = $isAr ? 'تنبيه إداري جديد' : 'New system alert';
                        }
                    @endphp
                    
                    <div class="p-3.5 flex items-start justify-between gap-3 transition-colors duration-150 relative {{ $isUnread ? 'bg-brand-50/25 dark:bg-brand-950/10' : 'bg-white dark:bg-slate-900 hover:bg-slate-50/50 dark:hover:bg-slate-800/20' }}">
                        <!-- Left side: Icon + text details -->
                        <a href="{{ $config['route'] }}" class="flex items-start gap-3 flex-1">
                            <!-- Rounded Module Icon Wrapper -->
                            <div class="w-8 h-8 rounded-full shrink-0 flex items-center justify-center border {{ $config['bg'] }}">
                                {!! $config['icon'] !!}
                            </div>
                            
                            <!-- Messages -->
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 leading-normal hover:text-brand-500 transition-colors">
                                    {{ $messageText }}
                                </p>
                                <div class="flex items-center gap-2 text-[10px] text-slate-400">
                                    <span>{{ $item->created_at->diffForHumans() }}</span>
                                    @if($isUnread)
                                        <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                                    @endif
                                </div>
                            </div>
                        </a>

                        <!-- Right side: Quick actions -->
                        @if($isUnread)
                            <div class="shrink-0 pl-2 rtl:pl-0 rtl:pr-2">
                                <form action="{{ route('notifications.mark-as-read', $item->id) }}" method="POST">
                                    @csrf
                                    <button 
                                        type="submit" 
                                        class="p-1 rounded-lg text-slate-400 hover:text-brand-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all focus:outline-none"
                                        title="{{ $isAr ? 'تحديد كمقروء' : 'Mark as read' }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

<!-- Dropdown toggle scripts -->
<script>
    function toggleNotificationsDropdown() {
        const menu = document.getElementById('notifications-dropdown-menu');
        if (menu) menu.classList.toggle('hidden');
    }

    // Close on click outside
    document.addEventListener('click', function(event) {
        const container = document.getElementById('notifications-dropdown-container');
        const menu = document.getElementById('notifications-dropdown-menu');
        if (container && menu && !container.contains(event.target)) {
            menu.classList.add('hidden');
        }
    });
</script>
