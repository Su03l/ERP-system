<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                    {{ app()->getLocale() === 'ar' ? 'مركز التنبيهات والبريد الموحد' : 'Notification Center & Alerts' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ app()->getLocale() === 'ar' ? 'متابعة الإشعارات الإدارية، تحديثات النظام، وتنبيهات الأمان.' : 'Track system logs, security updates, document expirations, and administrative tasks.' }}
                </p>
            </div>

            @if($tab === 'unread' && $notifications->total() > 0)
                <div>
                    <form method="POST" action="{{ route('notifications.mark-all-as-read') }}">
                        @csrf
                        <button type="submit" class="btn-secondary text-sm font-bold shadow-sm hover:text-brand-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>{{ app()->getLocale() === 'ar' ? 'تحديد الكل كمقروء' : 'Mark All as Read' }}</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </x-slot>

    <!-- Notification Tabs -->
    <div class="flex border-b border-slate-200 dark:border-slate-800 gap-6">
        <a 
            href="{{ route('notifications.index', ['tab' => 'unread']) }}" 
            class="pb-4 font-bold text-sm border-b-2 transition-all {{ $tab === 'unread' ? 'border-brand-600 text-brand-600 dark:text-brand-400 dark:border-brand-400' : 'border-transparent text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300' }} flex items-center gap-2"
        >
            <span>{{ app()->getLocale() === 'ar' ? 'غير مقروءة' : 'Unread' }}</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $tab === 'unread' ? 'bg-brand-50 text-brand-600 dark:bg-brand-950/40 dark:text-brand-400' : 'bg-slate-100 text-slate-400 dark:bg-slate-800' }}">
                {{ auth()->user()->unreadNotifications()->count() }}
            </span>
        </a>
        <a 
            href="{{ route('notifications.index', ['tab' => 'read']) }}" 
            class="pb-4 font-bold text-sm border-b-2 transition-all {{ $tab === 'read' ? 'border-brand-600 text-brand-600 dark:text-brand-400 dark:border-brand-400' : 'border-transparent text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300' }}"
        >
            {{ app()->getLocale() === 'ar' ? 'المقروءة' : 'Read' }}
        </a>
        <a 
            href="{{ route('notifications.index', ['tab' => 'all']) }}" 
            class="pb-4 font-bold text-sm border-b-2 transition-all {{ $tab === 'all' ? 'border-brand-600 text-brand-600 dark:text-brand-400 dark:border-brand-400' : 'border-transparent text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300' }}"
        >
            {{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}
        </a>
    </div>

    <!-- Notifications List -->
    <div class="space-y-4">
        @forelse($notifications as $n)
            @php
                $data = $n->data;
                $type = $data['type'] ?? 'general';
                
                // Visual configs
                $iconBg = 'bg-slate-50 dark:bg-slate-800 text-slate-500';
                $svgIcon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>';
                $title = __('general.notification');
                $desc = '';
                $actionLink = null;
                $actionText = '';
                
                if ($type === 'security_event') {
                    $iconBg = 'bg-rose-50 text-rose-600 dark:bg-rose-950/20 dark:text-rose-400';
                    $svgIcon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>';
                    $title = app()->getLocale() === 'ar' ? 'تنبيه حماية وأمان' : 'Security Policy Event';
                    $event = $data['event'] ?? '';
                    $ip = $data['ip_address'] ?? '127.0.0.1';
                    $desc = app()->getLocale() === 'ar' 
                        ? "تم رصد حدث أمني (العملية: {$event}) من عنوان الإنترنت {$ip}." 
                        : "A security action ({$event}) was triggered from IP address {$ip}.";
                        
                    $actionLink = route('audit-logs.index', ['ip_address' => $ip]);
                    $actionText = app()->getLocale() === 'ar' ? 'مراجعة سجلات التدقيق' : 'View Audit Trails';
                    
                } elseif ($type === 'document_expiry') {
                    $iconBg = 'bg-amber-50 text-amber-600 dark:bg-amber-950/20 dark:text-amber-400';
                    $svgIcon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>';
                    $docTitle = app()->getLocale() === 'ar' ? ($data['title_ar'] ?? 'مستند') : ($data['title_en'] ?? 'Document');
                    $title = app()->getLocale() === 'ar' ? 'تنبيه انتهاء صلاحية مستند' : 'Document Approaching Expiration';
                    $expiry = $data['expiry_date'] ?? '';
                    $desc = app()->getLocale() === 'ar'
                        ? "المستند الخاص بكم ({$docTitle}) يقترب من تاريخ انتهاء صلاحيته الفعلي في {$expiry}."
                        : "The linked enterprise file ({$docTitle}) is reaching its expiration limit on {$expiry}.";
                    
                    // Standard document folder or link placeholder
                    $actionLink = url('/documents');
                    $actionText = app()->getLocale() === 'ar' ? 'عرض المستندات' : 'Go to Documents';
                    
                } elseif ($type === 'report_export_ready') {
                    $iconBg = 'bg-teal-50 text-teal-600 dark:bg-teal-950/20 dark:text-teal-400';
                    $svgIcon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>';
                    $title = app()->getLocale() === 'ar' ? 'تصدير البيانات جاهز' : 'Data Export Ready';
                    $fileName = $data['file_name'] ?? 'export.csv';
                    $desc = app()->getLocale() === 'ar'
                        ? "تمت معالجة ملف تصدير البيانات المطلوب ({$fileName}) بنجاح وهو جاهز للتحميل المباشر."
                        : "Requested spreadsheet export ({$fileName}) has been compiled and is ready for download.";
                    
                    $actionLink = url('/exports-center');
                    $actionText = app()->getLocale() === 'ar' ? 'تحميل الملف الآن' : 'Download Spreadsheet';
                    
                } elseif ($type === 'subscription_expiry') {
                    $iconBg = 'bg-purple-50 text-purple-600 dark:bg-purple-950/20 dark:text-purple-400';
                    $svgIcon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                    $title = app()->getLocale() === 'ar' ? 'تنبيه تجديد الاشتراك' : 'ERP Subscription Expiring';
                    $expiry = $data['expiry_date'] ?? '';
                    $desc = app()->getLocale() === 'ar'
                        ? "ستنتهي صلاحية اشتراك الشركة السحابي الفعلي في {$expiry}. يرجى التجديد لتفادي إيقاف الخدمات."
                        : "Your tenant subscription period will terminate on {$expiry}. Please renew to prevent interruptions.";
                        
                    $actionLink = url('/company-subscriptions');
                    $actionText = app()->getLocale() === 'ar' ? 'تجديد الاشتراك الآن' : 'Renew ERP Plan';
                }
            @endphp

            <div class="erp-card p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 {{ $n->unread() ? 'border-s-4 border-s-brand-600 bg-brand-50/5 dark:bg-brand-950/5' : '' }}">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl {{ $iconBg }} flex items-center justify-center shrink-0">
                        {!! $svgIcon !!}
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center flex-wrap gap-2">
                            <h3 class="font-bold text-slate-900 dark:text-white text-sm">
                                {{ $title }}
                            </h3>
                            @if($n->unread())
                                <span class="inline-block text-[9px] bg-brand-100 text-brand-800 px-1.5 py-0.5 rounded-full font-bold">
                                    {{ app()->getLocale() === 'ar' ? 'جديد' : 'New' }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-400">
                            {{ $desc }}
                        </p>
                        <div class="flex items-center gap-3 pt-1 text-[10px] text-slate-400 font-semibold font-mono">
                            <span>{{ $n->created_at?->diffForHumans() }}</span>
                            <span>•</span>
                            <span>{{ $n->created_at?->format('Y-m-d H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 shrink-0 self-end md:self-center">
                    @if($actionLink)
                        <a href="{{ $actionLink }}" class="btn-secondary text-xs py-1.5 px-3 font-bold">
                            {{ $actionText }}
                        </a>
                    @endif

                    @if($n->unread())
                        <form method="POST" action="{{ route('notifications.mark-as-read', $n->id) }}">
                            @csrf
                            <button 
                                type="submit" 
                                class="p-2 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors"
                                title="{{ app()->getLocale() === 'ar' ? 'تحديد كمقروء' : 'Mark as read' }}"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="erp-card p-12 text-center">
                <div class="erp-empty-state">
                    <svg class="w-16 h-16 text-slate-300 dark:text-slate-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <h3 class="font-bold text-slate-700 dark:text-slate-300 text-sm mb-1">
                        {{ app()->getLocale() === 'ar' ? 'لا توجد تنبيهات متوفرة حالياً' : 'No Notifications Available' }}
                    </h3>
                    <p class="text-xs text-slate-400">
                        {{ app()->getLocale() === 'ar' ? 'عند تلقيك أي تنبيهات إدارية أو تنبيهات متعلقة بالحماية ستظهر هنا فوراً.' : 'All system updates, document actions, and security triggers will be logged here.' }}
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-5">
        {{ $notifications->links() }}
    </div>
</x-app-layout>
