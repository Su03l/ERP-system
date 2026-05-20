<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(protected AuditLogger $auditLogger) {}

    /**
     * Display a listing of the roles.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->hasPermission('roles.view', $user->company_id), 403);

        $roles = Role::where('company_id', $user->company_id)
            ->withCount('users')
            ->latest('id')
            ->paginate(15);

        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->hasPermission('roles.create', $user->company_id), 403);

        $permissions = Permission::all();

        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $role = DB::connection()->transaction(function () use ($validated, $user) {
            $role = Role::create([
                'company_id' => $user->company_id,
                'name' => $validated['name'],
                'key' => $validated['key'],
                'description' => $validated['description'] ?? null,
            ]);

            if (! empty($validated['permissions'])) {
                $permissionIds = Permission::whereIn('key', $validated['permissions'])->pluck('id')->all();
                $role->permissions()->sync($permissionIds);
            }

            $this->auditLogger->log(
                action: 'role.created',
                auditable: $role,
                newValues: $role->only(['name', 'key', 'description']),
                metadata: ['permissions_count' => count($validated['permissions'] ?? [])],
                user: $user,
                company: $user->company_id
            );

            return $role;
        });

        return redirect()->route('roles.index')->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء الدور بنجاح.' : 'Role created successfully.');
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role, Request $request): View
    {
        $user = $request->user();
        abort_unless($role->company_id === $user->company_id, 403);
        abort_unless($user->hasPermission('roles.update', $user->company_id), 403);

        $permissions = Permission::all();
        $rolePermissions = $role->permissions()->pluck('key')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        DB::connection()->transaction(function () use ($role, $validated, $user) {
            $oldValues = $role->only(['name', 'key', 'description']);

            $role->update([
                'name' => $validated['name'],
                'key' => $validated['key'],
                'description' => $validated['description'] ?? null,
            ]);

            if (isset($validated['permissions'])) {
                $permissionIds = Permission::whereIn('key', $validated['permissions'])->pluck('id')->all();
                $role->permissions()->sync($permissionIds);
            } else {
                $role->permissions()->sync([]);
            }

            $this->auditLogger->log(
                action: 'role.updated',
                auditable: $role,
                oldValues: $oldValues,
                newValues: $role->only(['name', 'key', 'description']),
                metadata: ['permissions_count' => count($validated['permissions'] ?? [])],
                user: $user,
                company: $user->company_id
            );
        });

        return redirect()->route('roles.index')->with('success', app()->getLocale() === 'ar' ? 'تم تحديث الدور بنجاح.' : 'Role updated successfully.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role, Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($role->company_id === $user->company_id, 403);
        abort_unless($user->hasPermission('roles.delete', $user->company_id), 403);

        // Protect active roles
        if ($role->users()->exists()) {
            return redirect()->route('roles.index')->with('error', app()->getLocale() === 'ar' ? 'لا يمكن حذف دور معين لمستخدمين نشطين.' : 'Cannot delete a role assigned to active users.');
        }

        // Protect system critical roles
        if (in_array($role->key, ['admin', 'administrator', 'owner'])) {
            return redirect()->route('roles.index')->with('error', app()->getLocale() === 'ar' ? 'لا يمكن حذف دور مدير النظام الأساسي.' : 'Cannot delete the system administrator role.');
        }

        DB::connection()->transaction(function () use ($role, $user) {
            $oldValues = $role->only(['name', 'key', 'description']);

            $role->permissions()->sync([]);
            $role->delete();

            $this->auditLogger->log(
                action: 'role.deleted',
                auditable: $role,
                oldValues: $oldValues,
                user: $user,
                company: $user->company_id
            );
        });

        return redirect()->route('roles.index')->with('success', app()->getLocale() === 'ar' ? 'تم حذف الدور بنجاح.' : 'Role deleted successfully.');
    }
}
