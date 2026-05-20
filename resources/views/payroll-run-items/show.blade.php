@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'تفاصيل بنود الراتب' : 'Payroll Item Details')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 rtl:space-x-reverse">
                    <li class="inline-flex items-center">
                        <a href="{{ route('payroll.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400 dark:hover:text-brand-400 transition-colors">
                            {{ app()->getLocale() === 'ar' ? 'لوحة تحكم الرواتب' : 'Payroll Dashboard' }}
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-slate-400 rtl:rotate-180" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                            <a href="{{ route('payroll-runs.show', $payrollRunItem->payroll_run_id) }}" class="ms-1 text-sm text-slate-500 hover:text-brand-600 dark:text-slate-400 dark:hover:text-brand-400 transition-colors">
                                {{ app()->getLocale() === 'ar' ? 'تشغيل الراتب' : 'Payroll Run' }} #{{ $payrollRunItem->payrollRun->run_number }}
                            </a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-slate-400 rtl:rotate-180" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                            <span class="ms-1 text-sm font-bold text-slate-900 dark:text-white">{{ $payrollRunItem->employee->first_name_ar ?? $payrollRunItem->employee->first_name_en }} {{ $payrollRunItem->employee->last_name_ar ?? $payrollRunItem->employee->last_name_en }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white flex items-center gap-3">
                {{ app()->getLocale() === 'ar' ? 'تفاصيل مستحقات الموظف' : 'Employee Payroll Details' }}
                <span class="px-3 py-1 text-xs font-bold rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 border border-blue-100 dark:border-blue-800 uppercase">
                    {{ $payrollRunItem->status->label() }}
                </span>
            </h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('payroll-run-items.payslip', $payrollRunItem->id) }}" class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-sm transition-all gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                {{ app()->getLocale() === 'ar' ? 'عرض كشف الراتب' : 'View Payslip' }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Summary and Stats -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm transition-all hover:shadow-md">
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ app()->getLocale() === 'ar' ? 'إجمالي المستحقات' : 'Gross Salary' }}</div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white">{{ number_format($payrollRunItem->gross_salary, 2) }} <span class="text-xs font-medium text-slate-400">{{ $payrollRunItem->company->currency ?? 'SAR' }}</span></div>
                    <div class="mt-2 text-xs font-semibold text-emerald-500 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        {{ app()->getLocale() === 'ar' ? 'قبل الاستقطاعات' : 'Before Deductions' }}
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm transition-all hover:shadow-md">
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ app()->getLocale() === 'ar' ? 'إجمالي الاستقطاعات' : 'Total Deductions' }}</div>
                    <div class="text-2xl font-black text-rose-600">{{ number_format($payrollRunItem->total_deductions, 2) }} <span class="text-xs font-medium text-slate-400">{{ $payrollRunItem->company->currency ?? 'SAR' }}</span></div>
                    <div class="mt-2 text-xs font-semibold text-rose-500 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                        {{ app()->getLocale() === 'ar' ? 'تشمل الضرائب والتأمينات' : 'Inc. Taxes & Social' }}
                    </div>
                </div>
                <div class="bg-brand-600 p-6 rounded-2xl border border-brand-500 shadow-lg shadow-brand-500/20 transform hover:scale-[1.02] transition-all">
                    <div class="text-xs font-bold text-brand-100 uppercase tracking-wider mb-2">{{ app()->getLocale() === 'ar' ? 'صافي الراتب' : 'Net Salary' }}</div>
                    <div class="text-3xl font-black text-white">{{ number_format($payrollRunItem->net_salary, 2) }} <span class="text-xs font-medium text-brand-200">{{ $payrollRunItem->company->currency ?? 'SAR' }}</span></div>
                    <div class="mt-2 text-xs font-bold text-white flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.64.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        {{ app()->getLocale() === 'ar' ? 'المبلغ القابل للصرف' : 'Disbursement Amount' }}
                    </div>
                </div>
            </div>

            <!-- Components Breakdown -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'تفصيل المكونات' : 'Components Breakdown' }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-6 py-4 text-start font-bold">{{ app()->getLocale() === 'ar' ? 'المكون' : 'Component' }}</th>
                                <th class="px-6 py-4 text-start font-bold">{{ app()->getLocale() === 'ar' ? 'النوع' : 'Type' }}</th>
                                <th class="px-6 py-4 text-end font-bold">{{ app()->getLocale() === 'ar' ? 'المبلغ' : 'Amount' }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'الراتب الأساسي' : 'Basic Salary' }}</td>
                                <td class="px-6 py-4 text-slate-500">{{ app()->getLocale() === 'ar' ? 'أساسي' : 'Basic' }}</td>
                                <td class="px-6 py-4 text-end font-black text-slate-900 dark:text-white">{{ number_format($payrollRunItem->basic_salary, 2) }}</td>
                            </tr>
                            @foreach($payrollRunItem->components as $component)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">{{ $component->name_ar ?? $component->name_en }}</td>
                                    <td class="px-6 py-4">
                                        @if($component->type === 'allowance')
                                            <span class="px-2.5 py-1 text-[10px] font-black uppercase rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800">
                                                {{ app()->getLocale() === 'ar' ? 'بدل' : 'Allowance' }}
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-[10px] font-black uppercase rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400 border border-rose-100 dark:border-rose-800">
                                                {{ app()->getLocale() === 'ar' ? 'استقطاع' : 'Deduction' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-end font-black {{ $component->type === 'allowance' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $component->type === 'allowance' ? '+' : '-' }}{{ number_format($component->amount, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                            
                            @if($payrollRunItem->overtime_amount > 0)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'العمل الإضافي' : 'Overtime' }}</td>
                                <td class="px-6 py-4"><span class="px-2.5 py-1 text-[10px] font-black uppercase rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800">{{ app()->getLocale() === 'ar' ? 'إضافي' : 'Overtime' }}</span></td>
                                <td class="px-6 py-4 text-end font-black text-emerald-600">+{{ number_format($payrollRunItem->overtime_amount, 2) }}</td>
                            </tr>
                            @endif

                            @if($payrollRunItem->attendance_deduction > 0)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'تأخير/غياب الحضور' : 'Attendance Late/Absence' }}</td>
                                <td class="px-6 py-4"><span class="px-2.5 py-1 text-[10px] font-black uppercase rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400 border border-rose-100 dark:border-rose-800">{{ app()->getLocale() === 'ar' ? 'استقطاع' : 'Deduction' }}</span></td>
                                <td class="px-6 py-4 text-end font-black text-rose-600">-{{ number_format($payrollRunItem->attendance_deduction, 2) }}</td>
                            </tr>
                            @endif

                            @if($payrollRunItem->leave_deduction > 0)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">{{ app()->getLocale() === 'ar' ? 'إجازات غير مدفوعة' : 'Unpaid Leaves' }}</td>
                                <td class="px-6 py-4"><span class="px-2.5 py-1 text-[10px] font-black uppercase rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400 border border-rose-100 dark:border-rose-800">{{ app()->getLocale() === 'ar' ? 'استقطاع' : 'Deduction' }}</span></td>
                                <td class="px-6 py-4 text-end font-black text-rose-600">-{{ number_format($payrollRunItem->leave_deduction, 2) }}</td>
                            </tr>
                            @endif
                        </tbody>
                        <tfoot class="bg-slate-50 dark:bg-slate-800/50 font-black border-t-2 border-slate-200 dark:border-slate-800">
                            <tr>
                                <td colspan="2" class="px-6 py-5 text-slate-900 dark:text-white text-base">{{ app()->getLocale() === 'ar' ? 'المجموع النهائي' : 'Final Total' }}</td>
                                <td class="px-6 py-5 text-end text-brand-600 dark:text-brand-400 text-lg">{{ number_format($payrollRunItem->net_salary, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Sidebar Info -->
        <div class="space-y-8">
            <!-- Employee Summary -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-6">
                <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-6 pb-2 border-b border-slate-100 dark:border-slate-800">
                    {{ app()->getLocale() === 'ar' ? 'ملخص الموظف' : 'Employee Summary' }}
                </h3>
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-2xl bg-brand-50 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400 font-black text-xl border border-brand-100 dark:border-brand-800">
                        {{ mb_substr($payrollRunItem->employee->first_name_ar ?? $payrollRunItem->employee->first_name_en, 0, 1) }}{{ mb_substr($payrollRunItem->employee->last_name_ar ?? $payrollRunItem->employee->last_name_en, 0, 1) }}
                    </div>
                    <div>
                        <div class="font-bold text-slate-900 dark:text-white text-lg leading-tight">{{ $payrollRunItem->employee->first_name_ar ?? $payrollRunItem->employee->first_name_en }} {{ $payrollRunItem->employee->last_name_ar ?? $payrollRunItem->employee->last_name_en }}</div>
                        <div class="text-sm text-slate-500 font-mono mt-1">#{{ $payrollRunItem->employee->employee_number }}</div>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'القسم' : 'Department' }}</div>
                        <div class="text-sm font-bold text-slate-700 dark:text-slate-300 mt-0.5">{{ $payrollRunItem->employee->department->name_ar ?? $payrollRunItem->employee->department->name_en ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'المسمى الوظيفي' : 'Job Title' }}</div>
                        <div class="text-sm font-bold text-slate-700 dark:text-slate-300 mt-0.5">{{ $payrollRunItem->employee->jobTitle->name_ar ?? $payrollRunItem->employee->jobTitle->name_en ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'تاريخ التعيين' : 'Hire Date' }}</div>
                        <div class="text-sm font-bold text-slate-700 dark:text-slate-300 mt-0.5">{{ $payrollRunItem->employee->hire_date?->format('Y-m-d') ?? '—' }}</div>
                    </div>
                </div>
            </div>

            <!-- Salary Package -->
            @php
                $packageId = data_get($payrollRunItem->metadata, 'salary_package_id');
                $package = $packageId ? \App\Models\EmployeeSalaryPackage::find($packageId) : null;
            @endphp
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10 rtl:right-auto rtl:left-0">
                    <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-6 pb-2 border-b border-slate-100 dark:border-slate-800">
                    {{ app()->getLocale() === 'ar' ? 'باقة الراتب' : 'Salary Package' }}
                </h3>
                @if($package)
                <div class="space-y-4">
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">{{ app()->getLocale() === 'ar' ? 'المسمى' : 'Package Name' }}</div>
                        <div class="text-sm font-black text-brand-600 dark:text-brand-400">{{ $package->name }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'فعال من' : 'Effective From' }}</div>
                            <div class="text-xs font-bold text-slate-700 dark:text-slate-300 mt-0.5">{{ $package->effective_from->format('Y-m-d') }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ app()->getLocale() === 'ar' ? 'العملة' : 'Currency' }}</div>
                            <div class="text-xs font-bold text-slate-700 dark:text-slate-300 mt-0.5">{{ $payrollRunItem->company->currency ?? 'SAR' }}</div>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-4 text-slate-400 italic text-sm">
                    {{ app()->getLocale() === 'ar' ? 'لا توجد معلومات باقة مفصلة' : 'No detailed package info' }}
                </div>
                @endif
            </div>

            <!-- Run Information -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-sm p-6 text-white">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">
                    {{ app()->getLocale() === 'ar' ? 'معلومات التشغيل' : 'Run Information' }}
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-400 font-medium">{{ app()->getLocale() === 'ar' ? 'رقم التشغيل' : 'Run Number' }}</span>
                        <span class="font-black">#{{ $payrollRunItem->payrollRun->run_number }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-400 font-medium">{{ app()->getLocale() === 'ar' ? 'الفترة' : 'Period' }}</span>
                        <span class="font-bold text-brand-400">{{ $payrollRunItem->payrollRun->payrollPeriod->name_ar ?? $payrollRunItem->payrollRun->payrollPeriod->name_en }}</span>
                    </div>
                    <div class="pt-3 border-t border-slate-800 flex items-center justify-between text-[10px] text-slate-500 uppercase font-bold tracking-widest">
                        <span>{{ app()->getLocale() === 'ar' ? 'تم الإنشاء في' : 'Generated At' }}</span>
                        <span>{{ $payrollRunItem->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
