<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full bg-slate-50 dark:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Nawwat ERP') }}</title>

    @fonts

    <!-- Scripts and Stylesheets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full text-slate-800 dark:text-slate-200 antialiased font-sans flex flex-col md:flex-row overflow-hidden">
    <!-- Responsive layout shell -->
    <div class="flex h-full w-full overflow-hidden" x-data="{ mobileSidebarOpen: false }">
        <!-- Sidebar Navigation Component -->
        <x-sidebar />

        <!-- Main Content Area Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
            <!-- Top Navigation Bar Component -->
            <x-topbar />

            <!-- Dynamic Breadcrumbs and Content Body -->
            <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-slate-50/50 dark:bg-slate-950/50 focus:outline-none">
                <div class="max-w-7xl mx-auto space-y-6">
                    <!-- Page Header / Breadcrumbs Section -->
                    @if (isset($header))
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-5">
                            <div>
                                {{ $header }}
                            </div>
                            @if (isset($actions))
                                <div class="flex items-center gap-3">
                                    {{ $actions }}
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Flash messages / Notifications -->
                    @if (session('success'))
                        <div class="p-4 mb-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 dark:bg-emerald-950/20 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50 flex items-center gap-2" role="alert">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="p-4 mb-4 text-sm text-rose-800 rounded-lg bg-rose-50 dark:bg-rose-950/20 dark:text-rose-400 border border-rose-100 dark:border-rose-900/50 flex items-center gap-2" role="alert">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    <!-- Page Primary Slot Content -->
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</body>
</html>
