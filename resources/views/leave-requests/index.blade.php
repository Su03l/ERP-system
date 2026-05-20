@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'طلبات الإجازات' : 'Leave Requests')

@section('content')
<div class="py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                {{ app()->getLocale() === 'ar' ? 'طلبات الإجازات' : 'Leave Requests' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ app()->getLocale() === 'ar' ? 'متابعة وإدارة طلبات الإجازات.' : 'Monitor and manage leave requests.' }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('leave.dashboard') }}" class="btn-secondary px-4 py-2">
                {{ app()->getLocale() === 'ar' ? 'لوحة الإجازات' : 'Dashboard' }}
            </a>
            <a href="{{ route('leave-requests.create') }}" class="btn-primary px-4 py-2 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                {{ app()->getLocale() === 'ar' ? 'طلب جديد' : 'New Request' }}
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
        
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
            <form action="{{ route('leave-requests.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ app()->getLocale() === 'ar' ? 'الموظف' : 'Employee ID' }}</label>
                    <input type="text" name="employee_id" value="{{ request('employee_id') }}" class="erp-input w-full text-sm py-2">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</label>
                    <select name="status" class="erp-input w-full text-sm py-2">
                        <option value="">{{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'مسودة' : 'Draft' }}</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'معلق' : 'Pending' }}</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'موافق عليه' : 'Approved' }}</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'مرفوض' : 'Rejected' }}</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'ملغى' : 'Cancelled' }}</option>
                        <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'مرجع' : 'Returned' }}</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn-secondary px-4 py-2 w-full text-sm">
                        {{ app()->getLocale() === 'ar' ? 'تصفية' : 'Filter' }}
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الموظف' : 'Employee' }}</th>
                        <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'نوع الإجازة' : 'Type' }}</th>
                        <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'من' : 'From' }}</th>
                        <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'إلى' : 'To' }}</th>
                        <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الأيام' : 'Days' }}</th>
                        <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                        <th class="px-6 py-4 text-right">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($leaveRequests as $req)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $req->employee->first_name }} {{ $req->employee->last_name }}</div>
                                <div class="text-xs text-slate-500">{{ $req->employee->employee_number }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 dark:text-slate-200">
                                    {{ app()->getLocale() === 'ar' ? $req->leaveType->name_ar : $req->leaveType->name_en }}
                                </div>
                            </td>
                            <td class="px-6 py-4 font-mono text-sm">{{ $req->start_date->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 font-mono text-sm">{{ $req->end_date->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 font-bold text-slate-700 dark:text-slate-300">
                                {{ floatval($req->total_days) }}
                            </td>
                            <td class="px-6 py-4">
                                @if($req->status->value === 'approved')
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                        {{ app()->getLocale() === 'ar' ? 'موافق عليه' : 'Approved' }}
                                    </span>
                                @elseif($req->status->value === 'pending')
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                                        {{ app()->getLocale() === 'ar' ? 'معلق' : 'Pending' }}
                                    </span>
                                @elseif($req->status->value === 'rejected')
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-bold bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">
                                        {{ app()->getLocale() === 'ar' ? 'مرفوض' : 'Rejected' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                        {{ ucfirst($req->status->value) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('leave-requests.show', $req->id) }}" class="text-brand-600 hover:text-brand-700 font-semibold px-2">
                                    {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}
                                </a>
                                @if($req->status->value === 'draft' || $req->status->value === 'returned')
                                    <a href="{{ route('leave-requests.edit', $req->id) }}" class="text-blue-600 hover:text-blue-700 font-semibold px-2">
                                        {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                {{ app()->getLocale() === 'ar' ? 'لا توجد طلبات.' : 'No leave requests found.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($leaveRequests->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">
                {{ $leaveRequests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
