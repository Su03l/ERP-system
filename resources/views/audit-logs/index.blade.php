<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                    {{ app()->getLocale() === 'ar' ? 'سجلات التدقيق والمراقبة' : 'Audit & Activity Trails' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ app()->getLocale() === 'ar' ? 'مراقبة التغييرات الأمنية، العمليات الحساسة، وجداول العمليات التشغيلية للشركة.' : 'Monitor system security changes, critical data mutations, and operational activities.' }}
                </p>
            </div>

            @if(auth()->user()->hasPermission('audit_logs.export', auth()->user()->company_id))
                <div>
                    <button 
                        type="button" 
                        onclick="alert('{{ app()->getLocale() === 'ar' ? 'التصدير قيد التحضير وسيتم توفيره قريباً كجزء من مركز التصدير الموحد.' : 'Export is being compiled and will be available in the unified exports center shortly.' }}')"
                        class="btn-primary shadow-md shadow-brand-500/10 active:scale-98 transition-transform font-bold text-sm"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>{{ app()->getLocale() === 'ar' ? 'تصدير السجلات (CSV)' : 'Export Trails (CSV)' }}</span>
                    </button>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Advanced Filtering System -->
        <div class="erp-card p-6">
            <form method="GET" action="{{ route('audit-logs.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- User Filter -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">
                            {{ app()->getLocale() === 'ar' ? 'الموظف / المستخدم' : 'Initiated By (User)' }}
                        </label>
                        <select name="user_id" class="erp-input">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'كل المستخدمين' : 'All Users' }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ ($filters['user_id'] ?? null) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Action Filter -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">
                            {{ app()->getLocale() === 'ar' ? 'نوع العملية' : 'Action Type' }}
                        </label>
                        <select name="action" class="erp-input">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'كل العمليات' : 'All Actions' }}</option>
                            @foreach($actions as $act)
                                <option value="{{ $act }}" {{ ($filters['action'] ?? null) === $act ? 'selected' : '' }}>
                                    {{ $act }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date From -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">
                            {{ app()->getLocale() === 'ar' ? 'من تاريخ' : 'Date From' }}
                        </label>
                        <input 
                            type="date" 
                            name="date_from" 
                            value="{{ $filters['date_from'] ?? '' }}" 
                            class="erp-input"
                        >
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">
                            {{ app()->getLocale() === 'ar' ? 'إلى تاريخ' : 'Date To' }}
                        </label>
                        <input 
                            type="date" 
                            name="date_to" 
                            value="{{ $filters['date_to'] ?? '' }}" 
                            class="erp-input"
                        >
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:justify-end gap-3 pt-2">
                    <a href="{{ route('audit-logs.index') }}" class="btn-secondary text-sm font-bold shadow-sm justify-center">
                        {{ app()->getLocale() === 'ar' ? 'إعادة ضبط الفلاتر' : 'Reset Filters' }}
                    </a>
                    <button type="submit" class="btn-primary text-sm font-bold shadow-sm justify-center">
                        {{ app()->getLocale() === 'ar' ? 'تطبيق التصفية' : 'Filter Trails' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Audit Table List -->
        <div class="erp-card p-6">
            <div class="erp-table-container">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>{{ app()->getLocale() === 'ar' ? 'التاريخ والوقت' : 'Timestamp' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'المبادر' : 'User Identity' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'العملية' : 'Action' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'السجل المتأثر' : 'Target Entity' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'عنوان IP' : 'IP Address' }}</th>
                            <th class="text-center">{{ app()->getLocale() === 'ar' ? 'التفاصيل' : 'Details' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="text-xs font-mono whitespace-nowrap text-slate-600 dark:text-slate-400">
                                    {{ $log->created_at?->format('Y-m-d H:i:s') }}
                                </td>
                                <td>
                                    @if($log->user)
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold flex items-center justify-center text-[10px] shrink-0">
                                                {{ mb_strtoupper(mb_substr($log->user->name, 0, 1)) }}
                                            </div>
                                            <a href="{{ route('users.show', $log->user->id) }}" class="font-bold text-slate-800 dark:text-white hover:text-brand-600 dark:hover:text-brand-400 transition-colors text-xs">
                                                {{ $log->user->name }}
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500 text-xs font-semibold">
                                            {{ app()->getLocale() === 'ar' ? 'النظام التلقائي' : 'System Automation' }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $actionLower = strtolower($log->action);
                                        $badgeClass = 'bg-slate-100 text-slate-700 border-slate-200';
                                        if (str_contains($actionLower, 'created') || str_contains($actionLower, 'store') || str_contains($actionLower, 'approve')) {
                                            $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/50';
                                        } elseif (str_contains($actionLower, 'updated') || str_contains($actionLower, 'update') || str_contains($actionLower, 'edit')) {
                                            $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-900/50';
                                        } elseif (str_contains($actionLower, 'deleted') || str_contains($actionLower, 'destroy') || str_contains($actionLower, 'reject') || str_contains($actionLower, 'revoke')) {
                                            $badgeClass = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/20 dark:text-rose-400 dark:border-rose-900/50';
                                        }
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded border text-[10px] font-mono font-bold {{ $badgeClass }}">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td>
                                    @if($log->auditable_type)
                                        @php
                                            $entityName = class_basename($log->auditable_type);
                                            $entityLink = null;
                                            
                                            if ($log->auditable_type === 'App\Models\User') {
                                                $entityLink = route('users.show', $log->auditable_id);
                                            } elseif ($log->auditable_type === 'App\Models\Role') {
                                                $entityLink = route('roles.edit', $log->auditable_id);
                                            } elseif ($log->auditable_type === 'App\Models\WorkflowInstance') {
                                                $entityLink = route('approvals.show', $log->auditable_id);
                                            }
                                        @endphp
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                                {{ $entityName }}
                                            </span>
                                            @if($entityLink)
                                                <a href="{{ $entityLink }}" class="text-xs font-mono font-bold text-brand-600 dark:text-brand-400 hover:underline">
                                                    #{{ $log->auditable_id }}
                                                </a>
                                            @else
                                                <span class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300">
                                                    #{{ $log->auditable_id }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="text-xs font-mono text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                    {{ $log->ip_address ?: '127.0.0.1' }}
                                </td>
                                <td class="text-center">
                                    <!-- Alpine-based simple metadata inspector -->
                                    <div x-data="{ open: false }" class="relative">
                                        <button 
                                            @click="open = true" 
                                            class="p-1 text-slate-400 dark:text-slate-500 hover:text-brand-600 dark:hover:text-brand-400 transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800"
                                            title="{{ app()->getLocale() === 'ar' ? 'عرض البيانات بالتفصيل' : 'View Payload Details' }}"
                                        >
                                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </button>

                                        <!-- Detail Modal Modal Backdrop -->
                                        <template x-teleport="body">
                                            <div 
                                                x-show="open" 
                                                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs"
                                                x-cloak
                                            >
                                                <!-- Modal panel -->
                                                <div 
                                                    @click.outside="open = false" 
                                                    class="w-full max-w-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl overflow-hidden flex flex-col max-h-[85vh]"
                                                >
                                                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                                        <h3 class="font-bold text-slate-900 dark:text-white text-base">
                                                            {{ app()->getLocale() === 'ar' ? 'تفاصيل سجل التدقيق' : 'Audit Trail Detailed Log' }}
                                                        </h3>
                                                        <button @click="open = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        </button>
                                                    </div>

                                                    <div class="p-6 overflow-y-auto space-y-5 text-start font-sans">
                                                        <div class="grid grid-cols-2 gap-4">
                                                            <div>
                                                                <span class="block text-[10px] text-slate-400 uppercase font-bold">{{ app()->getLocale() === 'ar' ? 'التوقيت' : 'Time' }}</span>
                                                                <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $log->created_at?->format('Y-m-d H:i:s') }}</span>
                                                            </div>
                                                            <div>
                                                                <span class="block text-[10px] text-slate-400 uppercase font-bold">{{ app()->getLocale() === 'ar' ? 'العملية' : 'Action' }}</span>
                                                                <span class="text-xs font-mono font-bold text-brand-600 dark:text-brand-400">{{ $log->action }}</span>
                                                            </div>
                                                            <div>
                                                                <span class="block text-[10px] text-slate-400 uppercase font-bold">{{ app()->getLocale() === 'ar' ? 'المستخدم' : 'User' }}</span>
                                                                <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $log->user?->name ?: 'System' }}</span>
                                                            </div>
                                                            <div>
                                                                <span class="block text-[10px] text-slate-400 uppercase font-bold">{{ app()->getLocale() === 'ar' ? 'عنوان IP' : 'IP Address' }}</span>
                                                                <span class="text-xs font-mono text-slate-800 dark:text-slate-200">{{ $log->ip_address }}</span>
                                                            </div>
                                                        </div>

                                                        @if($log->old_values)
                                                            <div class="space-y-1">
                                                                <span class="block text-[10px] text-rose-500 uppercase font-bold">{{ app()->getLocale() === 'ar' ? 'القيم القديمة (قبل التعديل)' : 'Old Values (Before)' }}</span>
                                                                <pre class="p-3 bg-rose-50 dark:bg-rose-950/20 text-rose-800 dark:text-rose-400 rounded-lg text-xs font-mono overflow-x-auto max-h-40">{!! json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}</pre>
                                                            </div>
                                                        @endif

                                                        @if($log->new_values)
                                                            <div class="space-y-1">
                                                                <span class="block text-[10px] text-emerald-500 uppercase font-bold">{{ app()->getLocale() === 'ar' ? 'القيم الجديدة (بعد التعديل)' : 'New Values (After)' }}</span>
                                                                <pre class="p-3 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-800 dark:text-emerald-400 rounded-lg text-xs font-mono overflow-x-auto max-h-40">{!! json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}</pre>
                                                            </div>
                                                        @endif

                                                        @if($log->metadata)
                                                            <div class="space-y-1">
                                                                <span class="block text-[10px] text-slate-400 uppercase font-bold">{{ app()->getLocale() === 'ar' ? 'البيانات الوصفية الإضافية' : 'Metadata' }}</span>
                                                                <pre class="p-3 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-lg text-xs font-mono overflow-x-auto max-h-40">{!! json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}</pre>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8">
                                    <div class="erp-empty-state">
                                        <svg class="w-12 h-12 text-slate-300 dark:text-slate-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <h3 class="font-bold text-slate-700 dark:text-slate-300 text-sm mb-1">
                                            {{ app()->getLocale() === 'ar' ? 'لم يتم العثور على سجلات تدقيق' : 'No Audit Logs Found' }}
                                        </h3>
                                        <p class="text-xs text-slate-400">
                                            {{ app()->getLocale() === 'ar' ? 'حاول تغيير معايير البحث أو تصفية التواريخ للبحث عن البيانات.' : 'Adjust search terms or query date range boundaries to locate historical logs.' }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div class="mt-5">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
