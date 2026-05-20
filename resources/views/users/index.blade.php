<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                    {{ app()->getLocale() === 'ar' ? 'إدارة حسابات المستخدمين' : 'User Accounts Directory' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ app()->getLocale() === 'ar' ? 'إدارة الهويات الرقمية للموظفين، وتعيين الأدوار، ومتابعة النشاط.' : 'Manage employee digital access, roles, and review connected profiles.' }}
                </p>
            </div>
            @if(auth()->user()->hasPermission('users.create', auth()->user()->company_id))
                <div>
                    <a href="{{ route('users.create') }}" class="btn-primary shadow-md shadow-brand-500/10 active:scale-98 transition-transform font-bold text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>{{ app()->getLocale() === 'ar' ? 'إضافة مستخدم جديد' : 'New User Account' }}</span>
                    </a>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="space-y-6">
        @if(session('success'))
            <div class="p-4 bg-teal-50 dark:bg-teal-950/20 border border-teal-200 dark:border-teal-900 rounded-xl text-teal-800 dark:text-teal-400 font-medium text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900 rounded-xl text-rose-800 dark:text-rose-400 font-medium text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Search Card -->
        <div class="erp-card p-4">
            <form method="GET" action="{{ route('users.index') }}" class="flex gap-3">
                <div class="relative flex-1">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $search }}" 
                        placeholder="{{ app()->getLocale() === 'ar' ? 'البحث بالاسم أو البريد الإلكتروني...' : 'Search by name, email...' }}" 
                        class="erp-input pl-10"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
                <button type="submit" class="btn-secondary text-sm font-bold shadow-sm shrink-0">
                    {{ app()->getLocale() === 'ar' ? 'بحث' : 'Search' }}
                </button>
            </form>
        </div>

        <!-- Users Table Card -->
        <div class="erp-card p-6">
            <div class="erp-table-container">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>{{ app()->getLocale() === 'ar' ? 'الاسم والمستخدم' : 'User Identity' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email Address' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'الأدوار المسندة' : 'Assigned Roles' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'الملف الوظيفي' : 'Employee File' }}</th>
                            <th class="text-center">{{ app()->getLocale() === 'ar' ? 'خيارات' : 'Options' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-brand-50 dark:bg-brand-950/20 text-brand-600 dark:text-brand-400 font-bold flex items-center justify-center text-xs shrink-0 select-none">
                                            {{ mb_strtoupper(mb_substr($u->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('users.show', $u->id) }}" class="font-bold text-slate-800 dark:text-white hover:text-brand-600 dark:hover:text-brand-400 transition-colors">
                                                {{ $u->name }}
                                            </a>
                                            @if($u->id === auth()->user()->id)
                                                <span class="inline-block text-[9px] bg-brand-100 text-brand-800 px-1.5 py-0.5 rounded-full font-bold ml-1.5 align-middle">
                                                    {{ app()->getLocale() === 'ar' ? 'أنت' : 'You' }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-xs font-mono text-slate-600 dark:text-slate-400">{{ $u->email }}</td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($u->roles as $role)
                                            <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded text-[10px] font-bold border border-slate-200 dark:border-slate-700">
                                                {{ $role->name }}
                                            </span>
                                        @empty
                                            <span class="text-slate-400 text-xs">—</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    @if($u->employeeProfile)
                                        <a href="/employees/{{ $u->employeeProfile->id }}" class="inline-flex items-center gap-1 text-xs text-brand-600 hover:text-brand-700 dark:text-brand-400 font-bold">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                                            <span>#{{ $u->employeeProfile->employee_code ?: $u->employeeProfile->id }}</span>
                                        </a>
                                    @else
                                        <span class="text-slate-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        @if(auth()->user()->hasPermission('users.view', auth()->user()->company_id))
                                            <a href="{{ route('users.show', $u->id) }}" class="text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-300 font-bold text-xs">
                                                {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}
                                            </a>
                                        @endif

                                        @if(auth()->user()->hasPermission('users.update', auth()->user()->company_id))
                                            <a href="{{ route('users.edit', $u->id) }}" class="text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300 font-bold text-xs">
                                                {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
                                            </a>
                                        @endif

                                        @if(auth()->user()->hasPermission('users.delete', auth()->user()->company_id) && $u->id !== auth()->user()->id)
                                            <form method="POST" action="{{ route('users.destroy', $u->id) }}" class="inline" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من حذف حساب هذا المستخدم؟' : 'Are you sure you want to delete this user account?' }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-500 hover:text-rose-700 font-bold text-xs cursor-pointer">
                                                    {{ app()->getLocale() === 'ar' ? 'حذف' : 'Delete' }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-12 text-slate-400">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        <span>{{ app()->getLocale() === 'ar' ? 'لا يوجد مستخدمين يطابقون معايير البحث.' : 'No users match the search criteria.' }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
