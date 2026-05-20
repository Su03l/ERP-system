<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('approvals.index') }}" class="p-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg transition-colors cursor-pointer">
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                        {{ app()->getLocale() === 'ar' ? 'تفاصيل ومعالجة طلب الاعتماد' : 'Review & Process Approval' }}
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        {{ app()->getLocale() === 'ar' ? 'مراجعة بيانات المعاملة، سجل الملاحظات، واتخاذ قرار معتمد.' : 'Examine workflow details, step activity logs, and dispatch decisions.' }}
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Right/Top Panels: Request Details & Payload -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Core Details -->
            <div class="erp-card p-6 space-y-6">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">
                            {{ app()->getLocale() === 'ar' ? 'تفاصيل المعاملة الأساسية' : 'Request Context' }}
                        </h3>
                        <p class="text-[10px] text-slate-400 mt-0.5">
                            {{ app()->getLocale() === 'ar' ? 'البيانات التعريفية الخاصة بطلب سير العمل الحالي.' : 'Core metadata detailing this workflow session.' }}
                        </p>
                    </div>
                    
                    @if($instance->status === 'pending')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-brand-50 border border-brand-200 text-brand-700 dark:bg-brand-950/30 dark:text-brand-400 dark:border-brand-900/60 rounded-full font-bold text-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"></span>
                            <span>{{ app()->getLocale() === 'ar' ? 'بانتظار الإجراء' : 'Action Required' }}</span>
                        </span>
                    @elseif($instance->status === 'completed')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-teal-50 border border-teal-200 text-teal-700 dark:bg-teal-950/30 dark:text-teal-400 dark:border-teal-900/60 rounded-full font-bold text-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                            <span>{{ app()->getLocale() === 'ar' ? 'مكتمل ومعتمد' : 'Approved' }}</span>
                        </span>
                    @elseif($instance->status === 'rejected')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-rose-50 border border-rose-200 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400 dark:border-rose-900/60 rounded-full font-bold text-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                            <span>{{ app()->getLocale() === 'ar' ? 'مرفوض' : 'Rejected' }}</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-amber-50 border border-amber-200 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400 dark:border-amber-900/60 rounded-full font-bold text-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            <span>{{ app()->getLocale() === 'ar' ? 'معاد للمراجعة' : 'Returned' }}</span>
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 block mb-1 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'اسم التدفق' : 'Workflow Name' }}</span>
                        <span class="text-sm font-bold text-slate-800 dark:text-white">{{ $instance->workflow ? $instance->workflow->name : '—' }}</span>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold text-slate-400 block mb-1 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'مقدم الطلب' : 'Submitted By' }}</span>
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-brand-50 dark:bg-brand-950/20 text-brand-600 dark:text-brand-400 text-[10px] font-bold flex items-center justify-center">
                                {{ $instance->requestedBy ? mb_strtoupper(mb_substr($instance->requestedBy->name, 0, 2)) : '—' }}
                            </div>
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-350">
                                {{ $instance->requestedBy ? $instance->requestedBy->name : '—' }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold text-slate-400 block mb-1 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'تاريخ البدء' : 'Initiated At' }}</span>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 font-mono">
                            {{ $instance->created_at ? $instance->created_at->format('Y-m-d H:i:s') : '—' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold text-slate-400 block mb-1 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'المرحلة النشطة الحالية' : 'Active Routing Step' }}</span>
                        <span class="text-xs font-bold text-brand-600 dark:text-brand-400">
                            {{ $instance->currentStep ? $instance->currentStep->name : (app()->getLocale() === 'ar' ? 'لا يوجد خطوة معلقة' : 'No pending step') }}
                        </span>
                    </div>
                </div>

                <!-- Subject / Payload Panel -->
                <div class="space-y-4 border-t border-slate-100 dark:border-slate-800/80 pt-6">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">
                            {{ app()->getLocale() === 'ar' ? 'البيانات التشغيلية المرفقة' : 'Operation Payload' }}
                        </h4>
                    </div>

                    @if($instance->payload && is_array($instance->payload))
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($instance->payload as $key => $value)
                                @if(is_scalar($value))
                                    <div class="p-3 bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800/60 rounded-xl space-y-1">
                                        <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">
                                            {{ str_replace('_', ' ', $key) }}
                                        </span>
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-350">
                                            @if(is_bool($value))
                                                {{ $value ? (app()->getLocale() === 'ar' ? 'نعم' : 'Yes') : (app()->getLocale() === 'ar' ? 'لا' : 'No') }}
                                            @else
                                                {{ $value }}
                                            @endif
                                        </span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 bg-slate-50 dark:bg-slate-800/20 rounded-xl border border-dashed border-slate-200 dark:border-slate-800 text-slate-400 text-xs">
                            {{ app()->getLocale() === 'ar' ? 'لا توجد بيانات تشغيلية إضافية مرفقة.' : 'No additional structured payload details are attached to this workflow instance.' }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Decision card (Approve, Reject, Return with comments) -->
            @if($canAct && $instance->status === 'pending')
                <div class="erp-card p-6 space-y-6 border-2 border-brand-500/20 dark:border-brand-500/10">
                    <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 bg-brand-500 rounded-full animate-ping"></span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">
                                {{ app()->getLocale() === 'ar' ? 'اتخاذ قرار معالجة الاعتماد' : 'Dispatch Authorization Decision' }}
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ app()->getLocale() === 'ar' ? 'أنت مفوض بالبت في هذه الخطوة. يرجى مراجعة التفاصيل بعناية قبل التأكيد.' : 'Your security clearance authorizes you to approve, reject, or return this request.' }}
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('approvals.decide', $instance->id) }}" class="space-y-4">
                        @csrf
                        
                        <div class="space-y-2">
                            <label for="comment" class="text-xs font-bold text-slate-700 dark:text-slate-350">
                                {{ app()->getLocale() === 'ar' ? 'ملاحظات وتبرير القرار (اختياري)' : 'Decision Rationale / Notes (Optional)' }}
                            </label>
                            <textarea 
                                name="comment" 
                                id="comment" 
                                rows="3" 
                                class="erp-input w-full p-3 resize-none text-xs"
                                placeholder="{{ app()->getLocale() === 'ar' ? 'اكتب أي ملاحظات أو توجيهات إضافية هنا لتسجيلها في سجل الإجراءات...' : 'Write specific reasons or notes about your decision here...' }}"
                            ></textarea>
                            <p class="text-[10px] text-slate-400">
                                {{ app()->getLocale() === 'ar' ? 'سيتم تسجيل هذه الملاحظات بشكل دائم في الجدول الزمني لسير المعاملة.' : 'Comments will be recorded permanently in the workflow timeline for audit purposes.' }}
                            </p>
                        </div>

                        <!-- Dynamic Action Buttons -->
                        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800/80 pt-4 mt-2">
                            <!-- Return button -->
                            <button type="submit" name="action" value="return" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-amber-50 hover:bg-amber-100/80 text-amber-700 dark:bg-amber-950/20 dark:hover:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-900 rounded-lg text-xs font-bold transition-all cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.334 4zM4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z"></path></svg>
                                <span>{{ app()->getLocale() === 'ar' ? 'إرجاع للمراجعة' : 'Return Request' }}</span>
                            </button>

                            <!-- Reject button -->
                            <button type="submit" name="action" value="reject" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-rose-50 hover:bg-rose-100/80 text-rose-700 dark:bg-rose-950/20 dark:hover:bg-rose-950/40 dark:text-rose-400 border border-rose-200 dark:border-rose-900 rounded-lg text-xs font-bold transition-all cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                <span>{{ app()->getLocale() === 'ar' ? 'رفض نهائي' : 'Reject Request' }}</span>
                            </button>

                            <!-- Approve button -->
                            <button type="submit" name="action" value="approve" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-md shadow-emerald-500/10 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                <span>{{ app()->getLocale() === 'ar' ? 'موافقة واعتماد' : 'Approve & Sign' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        <!-- Left/Sidebar Panel: Steps Timeline & Action History -->
        <div class="space-y-6">
            <!-- Timeline Tracker -->
            <div class="erp-card p-6 space-y-4">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">
                        {{ app()->getLocale() === 'ar' ? 'سلسلة خطوات سير المعاملة' : 'Workflow Steps' }}
                    </h3>
                </div>

                <div class="space-y-4">
                    @if($instance->workflow && $instance->workflow->steps)
                        @foreach($instance->workflow->steps as $step)
                            @php
                                $isCurrent = $instance->current_step_id === $step->id;
                                $isPassed = $instance->currentStep && $step->order < $instance->currentStep->order;
                                $isRejectedState = $instance->status === 'rejected';
                                $isCompletedState = $instance->status === 'completed';
                            @endphp
                            
                            <div class="flex items-start gap-3">
                                <!-- Marker -->
                                <div class="relative shrink-0 flex flex-col items-center">
                                    <div class="w-6 h-6 rounded-full border-2 text-[10px] font-bold flex items-center justify-center transition-colors
                                        {{ $isCurrent && !$isCompletedState && !$isRejectedState ? 'bg-brand-50 border-brand-500 text-brand-600 dark:bg-brand-950/20 dark:text-brand-400' : '' }}
                                        {{ $isPassed || $isCompletedState ? 'bg-emerald-500 border-emerald-500 text-white' : '' }}
                                        {{ !$isCurrent && !$isPassed && !$isCompletedState ? 'bg-slate-50 border-slate-200 text-slate-400 dark:bg-slate-800/40 dark:border-slate-700' : '' }}
                                        {{ $isRejectedState && $isCurrent ? 'bg-rose-500 border-rose-500 text-white' : '' }}
                                    ">
                                        @if($isPassed || $isCompletedState)
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                        @elseif($isRejectedState && $isCurrent)
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        @else
                                            {{ $step->order }}
                                        @endif
                                    </div>
                                    @if(!$loop->last)
                                        <div class="w-0.5 h-8 bg-slate-200 dark:bg-slate-700/80 my-1"></div>
                                    @endif
                                </div>

                                <!-- Label -->
                                <div>
                                    <span class="text-xs font-bold block leading-none 
                                        {{ $isCurrent ? 'text-brand-600 dark:text-brand-400' : 'text-slate-700 dark:text-slate-350' }}
                                    ">
                                        {{ $step->name }}
                                    </span>
                                    <span class="text-[9px] text-slate-400 block mt-1 uppercase font-semibold font-mono">
                                        {{ $step->approver_type }} : {{ $step->approver_value }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Action History Logs -->
            <div class="erp-card p-6 space-y-4">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wider">
                        {{ app()->getLocale() === 'ar' ? 'سجل القرارات التاريخية' : 'Audit Timeline' }}
                    </h3>
                </div>

                @forelse($instance->actions as $act)
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/30 border border-slate-100 dark:border-slate-800/80 rounded-xl space-y-1.5">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300">
                                {{ $act->actedBy ? $act->actedBy->name : '—' }}
                            </span>
                            <span class="text-[9px] font-mono text-slate-450">
                                {{ $act->acted_at ? $act->acted_at->format('Y-m-d H:i') : '—' }}
                            </span>
                        </div>

                        <!-- Step name context -->
                        <span class="text-[9px] bg-slate-100 dark:bg-slate-850 px-1.5 py-0.5 text-slate-500 rounded font-semibold block w-fit">
                            {{ $act->workflowStep ? $act->workflowStep->name : (app()->getLocale() === 'ar' ? 'تدشين الطلب' : 'Workflow Launch') }}
                        </span>

                        <div class="flex items-center gap-1">
                            @if($act->action === 'approved')
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span class="text-[10px] text-emerald-600 dark:text-emerald-450 font-bold">{{ app()->getLocale() === 'ar' ? 'وافق واعتمد' : 'Approved' }}</span>
                            @elseif($act->action === 'rejected')
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                <span class="text-[10px] text-rose-600 dark:text-rose-450 font-bold">{{ app()->getLocale() === 'ar' ? 'رفض المعاملة' : 'Rejected' }}</span>
                            @elseif($act->action === 'returned')
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                <span class="text-[10px] text-amber-600 dark:text-amber-450 font-bold">{{ app()->getLocale() === 'ar' ? 'أرجع للمراجعة' : 'Returned' }}</span>
                            @else
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                                <span class="text-[10px] text-slate-500 font-bold">{{ $act->action }}</span>
                            @endif
                        </div>

                        @if($act->comment)
                            <p class="text-[10px] text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-850/60 p-2 rounded-lg leading-normal font-sans italic">
                                "{{ $act->comment }}"
                            </p>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-400 text-xs">
                        {{ app()->getLocale() === 'ar' ? 'لا يوجد إجراءات أو تعليقات مسجلة حالياً.' : 'No steps have been processed on this session yet.' }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
