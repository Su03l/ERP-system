@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'كشف الراتب' : 'Payslip')

@push('styles')
<style>
    @media print {
        nav, header, aside, .no-print, .btn-primary, .btn-secondary, button { display: none !important; }
        body { background: white !important; margin: 0; padding: 0; }
        .print-container { box-shadow: none !important; border: none !important; max-width: 100% !important; width: 100% !important; margin: 0 !important; }
        .max-w-3xl { max-w: 100% !important; }
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <!-- Action Bar (No Print) -->
    <div class="mb-8 flex items-center justify-between no-print bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-brand-50 dark:bg-brand-900/30 flex items-center justify-center text-brand-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'كشف الراتب' : 'Employee Payslip' }}</h1>
                <p class="text-xs text-slate-500 font-medium tracking-wide uppercase">{{ $payrollRunItem->payrollRun->payrollPeriod->name_ar ?? $payrollRunItem->payrollRun->payrollPeriod->name_en }}</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-sm transition-all gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                {{ app()->getLocale() === 'ar' ? 'طباعة' : 'Print' }}
            </button>
            <a href="{{ route('payroll-run-items.show', $payrollRunItem->id) }}" class="inline-flex items-center px-4 py-2 text-sm font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-all">
                {{ app()->getLocale() === 'ar' ? 'عودة' : 'Back' }}
            </a>
        </div>
    </div>

    <!-- Payslip Container -->
    <div class="bg-white dark:bg-slate-900 shadow-xl border border-slate-200 dark:border-slate-800 rounded-3xl print-container overflow-hidden">
        <!-- Payslip Header -->
        <div class="p-8 sm:p-12 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row justify-between items-start gap-8 bg-gradient-to-br from-slate-50 to-white dark:from-slate-800/20 dark:to-slate-900">
            <div class="flex items-center gap-6">
                <!-- Company Logo Placeholder -->
                <div class="w-20 h-20 bg-brand-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-brand-200 dark:shadow-none">
                    <span class="text-3xl font-black">{{ mb_substr($payrollRunItem->company->name, 0, 1) }}</span>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ $payrollRunItem->company->name }}</h2>
                    <p class="text-sm text-slate-500 font-medium mt-1">{{ $payrollRunItem->company->legal_name ?? '' }}</p>
                    <div class="flex items-center gap-4 mt-3 text-xs text-slate-400 font-bold uppercase tracking-widest">
                        <span>{{ $payrollRunItem->company->email }}</span>
                        <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                        <span>{{ $payrollRunItem->company->phone }}</span>
                    </div>
                </div>
            </div>
            <div class="text-right rtl:text-left">
                <div class="inline-block px-4 py-1.5 rounded-full bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400 text-xs font-black uppercase tracking-widest border border-brand-100 dark:border-brand-800 mb-4">
                    {{ app()->getLocale() === 'ar' ? 'كشف راتب رسمي' : 'Official Payslip' }}
                </div>
                <div class="text-3xl font-black text-slate-900 dark:text-white">#{{ $payrollRunItem->payrollRun->run_number }}-{{ $payrollRunItem->id }}</div>
                <div class="text-xs font-bold text-slate-400 mt-1">{{ app()->getLocale() === 'ar' ? 'تاريخ الإصدار' : 'Issue Date' }}: {{ $payrollRunItem->created_at->format('Y-m-d') }}</div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="p-8 sm:p-12 bg-white dark:bg-slate-900">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <!-- Employee Section -->
                <div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <span class="w-6 h-0.5 bg-brand-500 rounded-full"></span>
                        {{ app()->getLocale() === 'ar' ? 'بيانات الموظف' : 'Employee Details' }}
                    </h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center"><span class="text-sm text-slate-500 font-bold">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</span><span class="text-sm font-black text-slate-900 dark:text-white">{{ $payrollRunItem->employee->first_name_ar ?? $payrollRunItem->employee->first_name_en }} {{ $payrollRunItem->employee->last_name_ar ?? $payrollRunItem->employee->last_name_en }}</span></div>
                        <div class="flex justify-between items-center"><span class="text-sm text-slate-500 font-bold">{{ app()->getLocale() === 'ar' ? 'الرقم الوظيفي' : 'Employee ID' }}</span><span class="text-sm font-black text-slate-900 dark:text-white">#{{ $payrollRunItem->employee->employee_number }}</span></div>
                        <div class="flex justify-between items-center"><span class="text-sm text-slate-500 font-bold">{{ app()->getLocale() === 'ar' ? 'المسمى الوظيفي' : 'Job Title' }}</span><span class="text-sm font-black text-slate-900 dark:text-white">{{ $payrollRunItem->employee->jobTitle->name_ar ?? $payrollRunItem->employee->jobTitle->name_en ?? '—' }}</span></div>
                        <div class="flex justify-between items-center"><span class="text-sm text-slate-500 font-bold">{{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}</span><span class="text-sm font-black text-slate-900 dark:text-white">{{ $payrollRunItem->employee->department->name_ar ?? $payrollRunItem->employee->department->name_en ?? '—' }}</span></div>
                    </div>
                </div>

                <!-- Period Section -->
                <div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <span class="w-6 h-0.5 bg-brand-500 rounded-full"></span>
                        {{ app()->getLocale() === 'ar' ? 'فترة الاستحقاق' : 'Payment Period' }}
                    </h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center"><span class="text-sm text-slate-500 font-bold">{{ app()->getLocale() === 'ar' ? 'الفترة' : 'Period' }}</span><span class="text-sm font-black text-slate-900 dark:text-white">{{ $payrollRunItem->payrollRun->payrollPeriod->name_ar ?? $payrollRunItem->payrollRun->payrollPeriod->name_en }}</span></div>
                        <div class="flex justify-between items-center"><span class="text-sm text-slate-500 font-bold">{{ app()->getLocale() === 'ar' ? 'من' : 'From' }}</span><span class="text-sm font-black text-slate-900 dark:text-white">{{ $payrollRunItem->payrollRun->payrollPeriod->starts_on->format('Y-m-d') }}</span></div>
                        <div class="flex justify-between items-center"><span class="text-sm text-slate-500 font-bold">{{ app()->getLocale() === 'ar' ? 'إلى' : 'To' }}</span><span class="text-sm font-black text-slate-900 dark:text-white">{{ $payrollRunItem->payrollRun->payrollPeriod->ends_on->format('Y-m-d') }}</span></div>
                        <div class="flex justify-between items-center"><span class="text-sm text-slate-500 font-bold">{{ app()->getLocale() === 'ar' ? 'أيام العمل' : 'Working Days' }}</span><span class="text-sm font-black text-slate-900 dark:text-white">{{ data_get($payrollRunItem->metadata, 'working_days', '—') }}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breakdown Section -->
        <div class="px-8 sm:px-12 py-10 bg-slate-50/50 dark:bg-slate-800/30 border-y border-slate-100 dark:border-slate-800">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
                <!-- Earnings -->
                <div class="space-y-6">
                    <h4 class="text-sm font-black text-emerald-600 uppercase tracking-widest flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ app()->getLocale() === 'ar' ? 'المستحقات' : 'Earnings' }}
                    </h4>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600 font-medium">{{ app()->getLocale() === 'ar' ? 'الراتب الأساسي' : 'Basic Salary' }}</span>
                            <span class="font-black text-slate-900 dark:text-white">{{ number_format($payrollRunItem->basic_salary, 2) }}</span>
                        </div>
                        @foreach($payrollRunItem->components->where('type', 'allowance') as $allowance)
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600 font-medium">{{ $allowance->name_ar ?? $allowance->name_en }}</span>
                            <span class="font-black text-slate-900 dark:text-white">{{ number_format($allowance->amount, 2) }}</span>
                        </div>
                        @endforeach
                        @if($payrollRunItem->overtime_amount > 0)
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600 font-medium">{{ app()->getLocale() === 'ar' ? 'العمل الإضافي' : 'Overtime' }}</span>
                            <span class="font-black text-slate-900 dark:text-white">{{ number_format($payrollRunItem->overtime_amount, 2) }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center">
                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'إجمالي المستحقات' : 'Total Earnings' }}</span>
                        <span class="text-lg font-black text-slate-900 dark:text-white">{{ number_format($payrollRunItem->gross_salary, 2) }}</span>
                    </div>
                </div>

                <!-- Deductions -->
                <div class="space-y-6">
                    <h4 class="text-sm font-black text-rose-600 uppercase tracking-widest flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ app()->getLocale() === 'ar' ? 'الاستقطاعات' : 'Deductions' }}
                    </h4>
                    <div class="space-y-4">
                        @foreach($payrollRunItem->components->where('type', 'deduction') as $deduction)
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600 font-medium">{{ $deduction->name_ar ?? $deduction->name_en }}</span>
                            <span class="font-black text-rose-600">{{ number_format($deduction->amount, 2) }}</span>
                        </div>
                        @endforeach
                        @if($payrollRunItem->attendance_deduction > 0)
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600 font-medium">{{ app()->getLocale() === 'ar' ? 'خصم الحضور' : 'Attendance' }}</span>
                            <span class="font-black text-rose-600">{{ number_format($payrollRunItem->attendance_deduction, 2) }}</span>
                        </div>
                        @endif
                        @if($payrollRunItem->leave_deduction > 0)
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-600 font-medium">{{ app()->getLocale() === 'ar' ? 'إجازات غير مدفوعة' : 'Unpaid Leave' }}</span>
                            <span class="font-black text-rose-600">{{ number_format($payrollRunItem->leave_deduction, 2) }}</span>
                        </div>
                        @endif
                        @if($payrollRunItem->components->where('type', 'deduction')->isEmpty() && $payrollRunItem->attendance_deduction == 0 && $payrollRunItem->leave_deduction == 0)
                        <div class="text-center py-4 text-slate-400 italic text-sm">
                            {{ app()->getLocale() === 'ar' ? 'لا توجد استقطاعات' : 'No deductions' }}
                        </div>
                        @endif
                    </div>
                    <div class="pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center">
                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'إجمالي الاستقطاعات' : 'Total Deductions' }}</span>
                        <span class="text-lg font-black text-rose-600">{{ number_format($payrollRunItem->total_deductions, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Total Footer -->
        <div class="p-10 sm:p-14 bg-slate-900 text-white flex flex-col sm:flex-row justify-between items-center gap-8">
            <div class="text-center sm:text-left rtl:sm:text-right">
                <div class="text-sm font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">{{ app()->getLocale() === 'ar' ? 'المبلغ الصافي المستحق' : 'Net Amount Payable' }}</div>
                <div class="text-5xl font-black text-brand-400 tracking-tighter">{{ number_format($payrollRunItem->net_salary, 2) }} <span class="text-lg text-slate-500">{{ $payrollRunItem->company->currency ?? 'SAR' }}</span></div>
            </div>
            <div class="hidden sm:block">
                <div class="w-32 h-32 rounded-3xl border-2 border-slate-800 flex flex-col items-center justify-center p-4">
                    <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 text-center">{{ app()->getLocale() === 'ar' ? 'ختم الشركة' : 'Company Seal' }}</div>
                    <div class="w-16 h-1 bg-slate-800 rounded-full"></div>
                </div>
            </div>
        </div>
        
        <!-- Footer Notes -->
        <div class="p-8 text-center border-t border-slate-100 dark:border-slate-800">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                {{ app()->getLocale() === 'ar' ? 'هذا المستند تم إنشاؤه آلياً ولا يتطلب توقيعاً' : 'This is a computer-generated document and does not require a signature' }}
            </p>
        </div>
    </div>
</div>
@endsection
