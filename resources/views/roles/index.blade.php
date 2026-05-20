<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight">
                    {{ app()->getLocale() === 'ar' ? 'الأدوار وصلاحيات الوصول (RBAC)' : 'Roles & Permissions (RBAC)' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ app()->getLocale() === 'ar' ? 'إدارة الأدوار الوظيفية داخل المؤسسة وتعيين الصلاحيات الدقيقة لكل دور.' : 'Define job roles, edit access control parameters and manage system scopes.' }}
                </p>
            </div>
            @if(auth()->user()->hasPermission('roles.create', auth()->user()->company_id))
                <div>
                    <a href="{{ route('roles.create') }}" class="btn-primary shadow-md shadow-brand-500/10 active:scale-98 transition-transform font-bold text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        <span>{{ app()->getLocale() === 'ar' ? 'إنشاء دور جديد' : 'New Role' }}</span>
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

        <div class="erp-card p-6">
            <div class="erp-table-container">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>{{ app()->getLocale() === 'ar' ? 'اسم الدور' : 'Role Name' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'رمز المعرف' : 'Key Identifier' }}</th>
                            <th>{{ app()->getLocale() === 'ar' ? 'الوصف' : 'Description' }}</th>
                            <th class="text-center">{{ app()->getLocale() === 'ar' ? 'المستخدمين النشطين' : 'Active Users' }}</th>
                            <th class="text-center">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td class="font-bold text-slate-800 dark:text-white">{{ $role->name }}</td>
                                <td>
                                    <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-md font-mono text-xs border border-slate-200 dark:border-slate-700">
                                        {{ $role->key }}
                                    </span>
                                </td>
                                <td class="text-slate-500 dark:text-slate-400 max-w-sm truncate" title="{{ $role->description }}">
                                    {{ $role->description ?: '—' }}
                                </td>
                                <td class="text-center font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $role->users_count }}
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        @if(auth()->user()->hasPermission('roles.update', auth()->user()->company_id))
                                            <a href="{{ route('roles.edit', $role->id) }}" class="text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300 font-bold text-xs">
                                                {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
                                            </a>
                                        @endif

                                        @if(auth()->user()->hasPermission('roles.delete', auth()->user()->company_id))
                                            <form method="POST" action="{{ route('roles.destroy', $role->id) }}" class="inline" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من حذف هذا الدور نهائياً؟' : 'Are you sure you want to delete this role permanently?' }}')">
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
                                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        <span>{{ app()->getLocale() === 'ar' ? 'لا توجد أدوار مضافة حالياً.' : 'No custom roles configured yet.' }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($roles->hasPages())
                <div class="mt-4">
                    {{ $roles->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
