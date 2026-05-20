@extends('layouts.app')
@section('title', app()->getLocale() === 'ar' ? 'تفاصيل تشغيل الرواتب' : 'Payroll Run Details')
@section('content')
<div class="py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-3">
                {{ app()->getLocale() === 'ar' ? 'تشغيل الرواتب' : 'Payroll Run' }} #{{ $payrollRun->run_number }}
                @if($payrollRun->status->value === 'approved' || $payrollRun->status->value === 'paid')<span class="px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">{{ ucfirst($payrollRun->status->value) }}</span>
                @elseif($payrollRun->status->value === 'pending')<span class="px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">{{ app()->getLocale() === 'ar' ? 'معلق' : 'Pending' }}</span>
                @elseif($payrollRun->status->value === 'rejected')<span class="px-2.5 py-1 rounded-md text-xs font-bold bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">{{ app()->getLocale() === 'ar' ? 'مرفوض' : 'Rejected' }}</span>
                @else<span class="px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">{{ ucfirst($payrollRun->status->value) }}</span>@endif
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ app()->getLocale() === 'ar' ? $payrollRun->payrollPeriod->name_ar : $payrollRun->payrollPeriod->name_en }} ({{ $payrollRun->payrollPeriod->starts_on->format('Y-m-d') }} → {{ $payrollRun->payrollPeriod->ends_on->format('Y-m-d') }})</p>
        </div>
        <a href="{{ route('payroll-runs.index') }}" class="btn-secondary px-4 py-2 text-sm">{{ app()->getLocale() === 'ar' ? 'عودة' : 'Back' }}</a>
    </div>
    @if(session('success'))<div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 text-sm font-semibold">{{ session('success') }}</div>@endif

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-8">
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'الإجمالي' : 'Gross' }}</div>
            <div class="text-xl font-extrabold text-slate-900 dark:text-white">{{ number_format($payrollRun->gross_amount, 2) }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'البدلات' : 'Allowances' }}</div>
            <div class="text-xl font-extrabold text-emerald-600">{{ number_format($payrollRun->total_allowances, 2) }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'الاستقطاعات' : 'Deductions' }}</div>
            <div class="text-xl font-extrabold text-rose-600">{{ number_format($payrollRun->total_deductions, 2) }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'الصافي' : 'Net' }}</div>
            <div class="text-xl font-extrabold text-brand-600">{{ number_format($payrollRun->net_amount, 2) }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'الموظفون' : 'Employees' }}</div>
            <div class="text-xl font-extrabold text-slate-900 dark:text-white">{{ $payrollRun->total_employees }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Employee Items Table -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'تفاصيل الموظفين' : 'Employee Details' }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-800"><tr>
                            <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الموظف' : 'Employee' }}</th>
                            <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الأساسي' : 'Basic' }}</th>
                            <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'البدلات' : 'Allow.' }}</th>
                            <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الخصومات' : 'Deduct.' }}</th>
                            <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الصافي' : 'Net' }}</th>
                            <th class="px-6 py-4 text-right">{{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}</th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @forelse($payrollRun->items as $item)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                    <td class="px-6 py-4"><div class="font-bold text-slate-900 dark:text-white">{{ $item->employee->first_name }} {{ $item->employee->last_name }}</div><div class="text-xs text-slate-500">{{ $item->employee->employee_number }}</div></td>
                                    <td class="px-6 py-4 font-mono">{{ number_format($item->basic_salary, 2) }}</td>
                                    <td class="px-6 py-4 font-mono text-emerald-600">{{ number_format($item->total_allowances, 2) }}</td>
                                    <td class="px-6 py-4 font-mono text-rose-600">{{ number_format($item->total_deductions, 2) }}</td>
                                    <td class="px-6 py-4 font-extrabold text-slate-900 dark:text-white">{{ number_format($item->net_salary, 2) }}</td>
                                    <td class="px-6 py-4 text-right"><a href="{{ route('payroll-run-items.show', $item->id) }}" class="text-brand-600 hover:text-brand-700 font-semibold">{{ app()->getLocale() === 'ar' ? 'تفاصيل' : 'Details' }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد عناصر.' : 'No items.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Actions Sidebar -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden sticky top-6">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'معلومات وإجراءات' : 'Info & Actions' }}</h2>
                </div>
                <div class="p-6 space-y-4">
                    <dl class="space-y-3 text-sm">
                        <div><dt class="text-slate-500 font-semibold">{{ app()->getLocale() === 'ar' ? 'أنشئ بواسطة' : 'Generated By' }}</dt><dd class="font-bold text-slate-900 dark:text-white">{{ $payrollRun->generatedBy?->name ?? '—' }}</dd></div>
                        <div><dt class="text-slate-500 font-semibold">{{ app()->getLocale() === 'ar' ? 'تاريخ الإنشاء' : 'Generated At' }}</dt><dd class="font-mono text-slate-700 dark:text-slate-300">{{ $payrollRun->generated_at?->format('Y-m-d H:i') ?? '—' }}</dd></div>
                        @if($payrollRun->approvedBy)<div><dt class="text-slate-500 font-semibold">{{ app()->getLocale() === 'ar' ? 'اعتمد بواسطة' : 'Approved By' }}</dt><dd class="font-bold text-slate-900 dark:text-white">{{ $payrollRun->approvedBy->name }}</dd></div>@endif
                    </dl>

                    @if(in_array($payrollRun->status->value, ['draft', 'pending']))
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-700 space-y-3">
                            <form action="{{ route('payroll-runs.approve', $payrollRun->id) }}" method="POST">@csrf
                                <input type="text" name="comment" placeholder="{{ app()->getLocale() === 'ar' ? 'تعليق (اختياري)' : 'Comment (optional)' }}" class="erp-input w-full text-sm py-2 mb-2">
                                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 rounded-lg transition-colors text-sm">{{ app()->getLocale() === 'ar' ? 'موافقة' : 'Approve' }}</button>
                            </form>
                            <form action="{{ route('payroll-runs.reject', $payrollRun->id) }}" method="POST">@csrf
                                <input type="text" name="reason" placeholder="{{ app()->getLocale() === 'ar' ? 'سبب الرفض' : 'Rejection reason' }}" required class="erp-input w-full text-sm py-2 mb-2">
                                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-semibold py-2 rounded-lg transition-colors text-sm">{{ app()->getLocale() === 'ar' ? 'رفض' : 'Reject' }}</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
