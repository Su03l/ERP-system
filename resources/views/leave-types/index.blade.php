@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'أنواع الإجازات' : 'Leave Types')

@section('content')
<div class="py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                {{ app()->getLocale() === 'ar' ? 'أنواع الإجازات' : 'Leave Types' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ app()->getLocale() === 'ar' ? 'إدارة سياسات وأنواع الإجازات المتاحة للموظفين.' : 'Manage leave policies and types available to employees.' }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('leave.dashboard') }}" class="btn-secondary px-4 py-2">
                {{ app()->getLocale() === 'ar' ? 'لوحة الإجازات' : 'Leave Dashboard' }}
            </a>
            <a href="{{ route('leave-types.create') }}" class="btn-primary px-4 py-2 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                {{ app()->getLocale() === 'ar' ? 'نوع إجازة جديد' : 'New Leave Type' }}
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 text-sm font-semibold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</th>
                        <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الكود' : 'Code' }}</th>
                        <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الأيام في السنة' : 'Days per Year' }}</th>
                        <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'مدفوعة؟' : 'Paid?' }}</th>
                        <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'تحتاج موافقة؟' : 'Approval?' }}</th>
                        <th class="px-6 py-4">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                        <th class="px-6 py-4 text-right">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($leaveTypes as $type)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                                {{ app()->getLocale() === 'ar' ? $type->name_ar : $type->name_en }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs">{{ $type->code }}</td>
                            <td class="px-6 py-4 font-bold">{{ $type->default_days_per_year + 0 }}</td>
                            <td class="px-6 py-4">
                                @if($type->is_paid)
                                    <span class="inline-flex items-center gap-1 text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 px-2 py-1 rounded-md text-xs font-bold">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        {{ app()->getLocale() === 'ar' ? 'نعم' : 'Yes' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-slate-500 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-md text-xs font-bold">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        {{ app()->getLocale() === 'ar' ? 'لا' : 'No' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($type->requires_approval)
                                    <span class="inline-flex items-center gap-1 text-amber-600 bg-amber-50 dark:bg-amber-900/20 px-2 py-1 rounded-md text-xs font-bold">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        {{ app()->getLocale() === 'ar' ? 'نعم' : 'Yes' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 px-2 py-1 rounded-md text-xs font-bold">
                                        {{ app()->getLocale() === 'ar' ? 'تلقائي' : 'Auto' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($type->status->value === 'active')
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                        {{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                        {{ app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('leave-types.edit', $type->id) }}" class="text-brand-600 hover:text-brand-700 font-semibold px-2">
                                    {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
                                </a>
                                <form action="{{ route('leave-types.destroy', $type->id) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من الحذف؟' : 'Are you sure you want to delete this?' }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-700 font-semibold px-2">
                                        {{ app()->getLocale() === 'ar' ? 'حذف' : 'Delete' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                {{ app()->getLocale() === 'ar' ? 'لا توجد أنواع إجازات مضافة بعد.' : 'No leave types added yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($leaveTypes->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">
                {{ $leaveTypes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
