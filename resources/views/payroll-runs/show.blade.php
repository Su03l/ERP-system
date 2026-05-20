@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'تفاصيل تشغيل الرواتب' : 'Payroll Run Details')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-brand-600 flex items-center justify-center text-white shadow-lg shadow-brand-500/20">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div>
                <h1 class="text-3xl font-black text-slate-900 dark:text-white flex items-center gap-3">
                    {{ app()->getLocale() === 'ar' ? 'تشغيل الرواتب' : 'Payroll Run' }} #{{ $payrollRun->run_number }}
                    <span class="px-3 py-1 text-xs font-bold rounded-full {{ $payrollRun->status->value === 'approved' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-amber-50 text-amber-600 border border-amber-100' }}">
                        {{ $payrollRun->status->label() }}
                    </span>
                </h1>
                <p class="text-sm text-slate-500 font-medium mt-1">
                    {{ app()->getLocale() === 'ar' ? $payrollRun->payrollPeriod->name_ar : $payrollRun->payrollPeriod->name_en }}
                    <span class="mx-2 text-slate-300">|</span>
                    {{ $payrollRun->payrollPeriod->starts_on->format('Y-m-d') }} → {{ $payrollRun->payrollPeriod->ends_on->format('Y-m-d') }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-1 shadow-sm">
                <button class="p-2 text-slate-500 hover:text-brand-600 transition-colors tooltip" title="{{ app()->getLocale() === 'ar' ? 'تصدير PDF' : 'Export PDF' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                </button>
                <button class="p-2 text-slate-500 hover:text-emerald-600 transition-colors tooltip" title="{{ app()->getLocale() === 'ar' ? 'تصدير Excel' : 'Export Excel' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </button>
            </div>
            <a href="{{ route('payroll-runs.index') }}" class="inline-flex items-center px-4 py-2.5 text-sm font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-all">
                {{ app()->getLocale() === 'ar' ? 'عودة' : 'Back' }}
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-8 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm font-bold flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- KPI Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm transition-all hover:shadow-md">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">{{ app()->getLocale() === 'ar' ? 'إجمالي الرواتب' : 'Total Gross' }}</div>
            <div class="text-2xl font-black text-slate-900 dark:text-white">{{ number_format($payrollRun->gross_amount, 2) }} <span class="text-xs font-medium text-slate-400">SAR</span></div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm transition-all hover:shadow-md">
            <div class="text-xs font-bold text-emerald-500 uppercase tracking-widest mb-3">{{ app()->getLocale() === 'ar' ? 'البدلات' : 'Allowances' }}</div>
            <div class="text-2xl font-black text-emerald-600">{{ number_format($payrollRun->total_allowances, 2) }} <span class="text-xs font-medium text-slate-400">SAR</span></div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm transition-all hover:shadow-md">
            <div class="text-xs font-bold text-rose-500 uppercase tracking-widest mb-3">{{ app()->getLocale() === 'ar' ? 'الاستقطاعات' : 'Deductions' }}</div>
            <div class="text-2xl font-black text-rose-600">{{ number_format($payrollRun->total_deductions, 2) }} <span class="text-xs font-medium text-slate-400">SAR</span></div>
        </div>
        <div class="bg-brand-600 p-6 rounded-2xl border border-brand-500 shadow-lg shadow-brand-500/20 transform hover:scale-[1.02] transition-all">
            <div class="text-xs font-bold text-brand-100 uppercase tracking-widest mb-3">{{ app()->getLocale() === 'ar' ? 'صافي المبلغ' : 'Net Amount' }}</div>
            <div class="text-3xl font-black text-white">{{ number_format($payrollRun->net_amount, 2) }} <span class="text-xs font-medium text-brand-200">SAR</span></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content: Employee Table -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'بنود رواتب الموظفين' : 'Employee Salary Items' }}</h2>
                    <span class="text-xs font-bold text-slate-500 bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-full border border-slate-200 dark:border-slate-700">
                        {{ $payrollRun->total_employees }} {{ app()->getLocale() === 'ar' ? 'موظف' : 'Employees' }}
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-6 py-4 text-start font-bold">{{ app()->getLocale() === 'ar' ? 'الموظف' : 'Employee' }}</th>
                                <th class="px-6 py-4 text-end font-bold">{{ app()->getLocale() === 'ar' ? 'الأساسي' : 'Basic' }}</th>
                                <th class="px-6 py-4 text-end font-bold">{{ app()->getLocale() === 'ar' ? 'الصافي' : 'Net' }}</th>
                                <th class="px-6 py-4 text-center font-bold">{{ app()->getLocale() === 'ar' ? 'الإجراء' : 'Action' }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($payrollRun->items as $item)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 font-black text-xs uppercase border border-slate-200 dark:border-slate-700">
                                                {{ mb_substr($item->employee->first_name, 0, 1) }}{{ mb_substr($item->employee->last_name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-900 dark:text-white">{{ $item->employee->first_name }} {{ $item->employee->last_name }}</div>
                                                <div class="text-xs text-slate-400 font-mono">#{{ $item->employee->employee_number }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-end font-bold text-slate-600 dark:text-slate-400">{{ number_format($item->basic_salary, 2) }}</td>
                                    <td class="px-6 py-4 text-end font-black text-slate-900 dark:text-white">{{ number_format($item->net_salary, 2) }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('payroll-run-items.show', $item->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-400 hover:text-brand-600 hover:border-brand-200 transition-all shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12">
                                        <x-empty-state-card 
                                            :title="app()->getLocale() === 'ar' ? 'لا توجد بيانات موظفين' : 'No employee data'"
                                            :description="app()->getLocale() === 'ar' ? 'لم يتم العثور على أي بنود رواتب لهذا التشغيل.' : 'No salary items found for this payroll run.'"
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Workflow Timeline (Task 271) -->
            @if($payrollRun->workflowInstance)
                <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden p-6">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-8 flex items-center gap-2">
                        <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ app()->getLocale() === 'ar' ? 'مسار الاعتمادات والموافقات' : 'Approval Workflow Timeline' }}
                    </h2>
                    
                    <div class="relative space-y-8">
                        @foreach($payrollRun->workflowInstance->workflow->steps as $step)
                            @php
                                $isCurrent = $payrollRun->workflowInstance->current_step_id === $step->id;
                                $isPassed = $step->order < ($payrollRun->workflowInstance->currentStep->order ?? 999);
                                $isCompleted = $payrollRun->workflowInstance->status === 'completed';
                                $isRejected = $payrollRun->workflowInstance->status === 'rejected';
                                $stepAction = $payrollRun->workflowInstance->actions->where('workflow_step_id', $step->id)->first();
                            @endphp
                            
                            <div class="flex items-start gap-6 relative">
                                @if(!$loop->last)
                                    <div class="absolute top-10 {{ app()->getLocale() === 'ar' ? 'right-5' : 'left-5' }} w-0.5 h-full bg-slate-100 dark:bg-slate-800"></div>
                                @endif
                                
                                <div class="w-10 h-10 rounded-full border-2 flex items-center justify-center shrink-0 z-10 transition-all duration-300
                                    {{ ($isPassed || $isCompleted) ? 'bg-emerald-500 border-emerald-500 text-white shadow-lg shadow-emerald-500/20' : '' }}
                                    {{ ($isCurrent && !$isCompleted && !$isRejected) ? 'bg-brand-600 border-brand-600 text-white shadow-lg shadow-brand-500/20 animate-pulse' : '' }}
                                    {{ ($isCurrent && $isRejected) ? 'bg-rose-500 border-rose-500 text-white shadow-lg shadow-rose-500/20' : '' }}
                                    {{ (!$isPassed && !$isCurrent && !$isCompleted) ? 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-400' : '' }}
                                ">
                                    @if($isPassed || $isCompleted)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    @elseif($isCurrent && $isRejected)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    @else
                                        <span class="text-sm font-black">{{ $step->order }}</span>
                                    @endif
                                </div>

                                <div class="flex-1 pt-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">{{ $step->name }}</h3>
                                        @if($stepAction)
                                            <span class="text-[10px] font-bold text-slate-400 uppercase font-mono">{{ $stepAction->created_at->format('Y-m-d H:i') }}</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-500 font-medium leading-relaxed">
                                        {{ app()->getLocale() === 'ar' ? 'المعتمد' : 'Approver' }}: 
                                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $step->approver_type }} ({{ $step->approver_value }})</span>
                                    </p>
                                    @if($stepAction && $stepAction->comment)
                                        <div class="mt-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-400 italic">
                                            "{{ $stepAction->comment }}"
                                            <div class="mt-1 font-bold text-slate-400 not-italic">— {{ $stepAction->actedBy->name }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar Actions -->
        <div class="space-y-8">
            <!-- Summary Sidebar -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-6">
                <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-6 pb-2 border-b border-slate-100 dark:border-slate-800">
                    {{ app()->getLocale() === 'ar' ? 'تفاصيل السجل' : 'Record Details' }}
                </h3>
                <div class="space-y-6">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'بواسطة' : 'Generated By' }}</div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="w-6 h-6 rounded-full bg-brand-50 flex items-center justify-center text-brand-600 font-bold text-[10px] border border-brand-100">
                                {{ mb_substr($payrollRun->generatedBy->name ?? '?', 0, 1) }}
                            </div>
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $payrollRun->generatedBy->name ?? '—' }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'تاريخ الإنشاء' : 'Generation Date' }}</div>
                        <div class="text-sm font-bold text-slate-700 dark:text-slate-300 mt-1 font-mono">{{ $payrollRun->generated_at?->format('Y-m-d H:i') ?? '—' }}</div>
                    </div>
                    @if($payrollRun->approvedBy)
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                        <div class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'تم الاعتماد بواسطة' : 'Final Approval By' }}</div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="w-6 h-6 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600 font-bold text-[10px] border border-emerald-100">
                                {{ mb_substr($payrollRun->approvedBy->name, 0, 1) }}
                            </div>
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $payrollRun->approvedBy->name }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Approval Actions (Task 271) -->
            @if(in_array($payrollRun->status->value, ['draft', 'pending']))
                <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-sm p-6 text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    </div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-6">
                        {{ app()->getLocale() === 'ar' ? 'إجراءات الاعتماد' : 'Approval Actions' }}
                    </h3>
                    
                    <form action="{{ route('payroll-runs.approve', $payrollRun->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-2">{{ app()->getLocale() === 'ar' ? 'ملاحظات الموافقة' : 'Approval Notes' }}</label>
                            <textarea name="comment" rows="2" class="w-full bg-slate-800 border-slate-700 rounded-xl text-xs focus:ring-brand-500 focus:border-brand-500 placeholder-slate-600" placeholder="{{ app()->getLocale() === 'ar' ? 'تعليق اختياري...' : 'Optional comment...' }}"></textarea>
                        </div>
                        <button type="submit" class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-black shadow-lg shadow-emerald-600/20 transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            {{ app()->getLocale() === 'ar' ? 'موافقة واعتماد' : 'Approve & Finalize' }}
                        </button>
                    </form>

                    <div class="mt-6 pt-6 border-t border-slate-800">
                        <form action="{{ route('payroll-runs.reject', $payrollRun->id) }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-2">{{ app()->getLocale() === 'ar' ? 'سبب الرفض' : 'Rejection Reason' }}</label>
                                <textarea name="reason" required rows="2" class="w-full bg-slate-800 border-slate-700 rounded-xl text-xs focus:ring-rose-500 focus:border-rose-500 placeholder-slate-600" placeholder="{{ app()->getLocale() === 'ar' ? 'يجب ذكر سبب الرفض...' : 'Must provide a reason...' }}"></textarea>
                            </div>
                            <button type="submit" class="w-full py-3 px-4 bg-transparent border-2 border-rose-500/30 text-rose-500 hover:bg-rose-500/10 rounded-xl text-sm font-black transition-all">
                                {{ app()->getLocale() === 'ar' ? 'رفض التشغيل' : 'Reject Payroll' }}
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
