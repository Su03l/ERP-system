@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'تفاصيل طلب الإجازة' : 'Leave Request Details')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-3">
                {{ app()->getLocale() === 'ar' ? 'تفاصيل طلب الإجازة' : 'Leave Request Details' }}
                
                @if($leaveRequest->status->value === 'approved')
                    <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        {{ app()->getLocale() === 'ar' ? 'موافق عليه' : 'Approved' }}
                    </span>
                @elseif($leaveRequest->status->value === 'pending')
                    <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                        {{ app()->getLocale() === 'ar' ? 'معلق للاعتماد' : 'Pending Approval' }}
                    </span>
                @elseif($leaveRequest->status->value === 'rejected')
                    <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">
                        {{ app()->getLocale() === 'ar' ? 'مرفوض' : 'Rejected' }}
                    </span>
                @elseif($leaveRequest->status->value === 'draft')
                    <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                        {{ app()->getLocale() === 'ar' ? 'مسودة' : 'Draft' }}
                    </span>
                @else
                    <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                        {{ ucfirst($leaveRequest->status->value) }}
                    </span>
                @endif
            </h1>
        </div>
        <a href="{{ route('leave-requests.index') }}" class="btn-secondary px-4 py-2 text-sm">
            {{ app()->getLocale() === 'ar' ? 'عودة للطلبات' : 'Back to Requests' }}
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 text-sm font-semibold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Details -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'معلومات الإجازة' : 'Leave Information' }}</h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-6">
                        <div>
                            <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ app()->getLocale() === 'ar' ? 'الموظف' : 'Employee' }}</dt>
                            <dd class="text-base font-bold text-slate-900 dark:text-white">{{ $leaveRequest->employee->first_name }} {{ $leaveRequest->employee->last_name }} ({{ $leaveRequest->employee->employee_number }})</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ app()->getLocale() === 'ar' ? 'نوع الإجازة' : 'Leave Type' }}</dt>
                            <dd class="text-base font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? $leaveRequest->leaveType->name_ar : $leaveRequest->leaveType->name_en }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ app()->getLocale() === 'ar' ? 'المدة' : 'Duration' }}</dt>
                            <dd class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <span>{{ $leaveRequest->start_date->format('Y-m-d') }}</span>
                                <svg class="w-4 h-4 text-slate-400 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                <span>{{ $leaveRequest->end_date->format('Y-m-d') }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ app()->getLocale() === 'ar' ? 'إجمالي الأيام' : 'Total Days' }}</dt>
                            <dd class="text-base font-bold text-brand-600 dark:text-brand-400">{{ floatval($leaveRequest->total_days) }} {{ app()->getLocale() === 'ar' ? 'يوم' : 'Days' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ app()->getLocale() === 'ar' ? 'السبب' : 'Reason' }}</dt>
                            <dd class="text-base text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 p-4 rounded-lg mt-1 border border-slate-100 dark:border-slate-700 whitespace-pre-wrap">{{ $leaveRequest->reason }}</dd>
                        </div>
                        
                        @if($leaveRequest->attachment_path)
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ app()->getLocale() === 'ar' ? 'المرفقات' : 'Attachment' }}</dt>
                                <dd class="mt-2">
                                    <a href="#" class="inline-flex items-center gap-2 text-sm font-bold text-brand-600 hover:text-brand-700 bg-brand-50 dark:bg-brand-900/20 px-4 py-2 rounded-lg border border-brand-100 dark:border-brand-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                        {{ app()->getLocale() === 'ar' ? 'عرض المرفق' : 'View Attachment' }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
            
            @if($leaveRequest->workflowInstance)
                <!-- Approval History Component -->
                <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'سجل الاعتمادات' : 'Approval History' }}</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-6">
                            @foreach($leaveRequest->workflowInstance->steps as $step)
                                <div class="relative pl-6 sm:pl-8 py-2">
                                    <div class="absolute top-4 left-0 w-2 h-2 rounded-full {{ $step->status === 'approved' ? 'bg-emerald-500' : ($step->status === 'rejected' ? 'bg-rose-500' : 'bg-slate-300 dark:bg-slate-600') }} ring-4 ring-white dark:ring-slate-900"></div>
                                    @if(!$loop->last)
                                        <div class="absolute top-6 left-1 w-px h-full bg-slate-200 dark:bg-slate-700 -translate-x-[0.5px]"></div>
                                    @endif
                                    
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $step->name }}</p>
                                            @if($step->comment)
                                                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1 italic">"{{ $step->comment }}"</p>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-500 font-semibold bg-slate-50 dark:bg-slate-800 px-2 py-1 rounded w-max">
                                            {{ $step->status }} 
                                            @if($step->acted_at)
                                                • {{ \Carbon\Carbon::parse($step->acted_at)->diffForHumans() }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column: Actions -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden sticky top-6">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</h2>
                </div>
                <div class="p-6 space-y-4">
                    
                    @if($leaveRequest->status->value === 'draft' || $leaveRequest->status->value === 'returned')
                        <form action="{{ route('leave-requests.submit', $leaveRequest->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-primary w-full py-2.5">
                                {{ app()->getLocale() === 'ar' ? 'تقديم الطلب للاعتماد' : 'Submit for Approval' }}
                            </button>
                        </form>
                        <a href="{{ route('leave-requests.edit', $leaveRequest->id) }}" class="btn-secondary w-full py-2.5 text-center block">
                            {{ app()->getLocale() === 'ar' ? 'تعديل الطلب' : 'Edit Request' }}
                        </a>
                    @endif

                    @if($leaveRequest->status->value === 'pending')
                        <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                            <p class="text-xs font-semibold text-slate-500 mb-3 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'للمدراء فقط' : 'Managers Only' }}</p>
                            
                            <form action="{{ route('leave-requests.approve', $leaveRequest->id) }}" method="POST" class="mb-3">
                                @csrf
                                <input type="text" name="comment" placeholder="{{ app()->getLocale() === 'ar' ? 'تعليق (اختياري)' : 'Comment (Optional)' }}" class="erp-input w-full text-sm py-2 mb-2">
                                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 rounded-lg transition-colors text-sm shadow-sm">
                                    {{ app()->getLocale() === 'ar' ? 'موافقة' : 'Approve' }}
                                </button>
                            </form>
                            
                            <form action="{{ route('leave-requests.reject', $leaveRequest->id) }}" method="POST">
                                @csrf
                                <input type="text" name="reason" placeholder="{{ app()->getLocale() === 'ar' ? 'سبب الرفض (مطلوب)' : 'Rejection Reason (Required)' }}" required class="erp-input w-full text-sm py-2 mb-2 border-rose-200 focus:border-rose-500 focus:ring-rose-500">
                                <div class="flex gap-2">
                                    <button type="submit" class="flex-1 bg-rose-600 hover:bg-rose-700 text-white font-semibold py-2 rounded-lg transition-colors text-sm shadow-sm">
                                        {{ app()->getLocale() === 'ar' ? 'رفض' : 'Reject' }}
                                    </button>
                                    <button type="submit" formaction="{{ route('leave-requests.return', $leaveRequest->id) }}" name="comment" value="Please revise" class="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2 rounded-lg transition-colors text-sm shadow-sm" formnovalidate>
                                        {{ app()->getLocale() === 'ar' ? 'إرجاع للمراجعة' : 'Return' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                    
                    @if($leaveRequest->status->value !== 'cancelled' && $leaveRequest->status->value !== 'rejected')
                        <form action="{{ route('leave-requests.cancel', $leaveRequest->id) }}" method="POST" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من إلغاء الطلب؟' : 'Are you sure you want to cancel this request?' }}');">
                            @csrf
                            <button type="submit" class="w-full text-rose-600 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-500/10 font-semibold py-2 rounded-lg transition-colors text-sm mt-4">
                                {{ app()->getLocale() === 'ar' ? 'إلغاء الطلب' : 'Cancel Request' }}
                            </button>
                        </form>
                    @endif

                </div>
            </div>
        </div>
        
    </div>
</div>
@endsection
