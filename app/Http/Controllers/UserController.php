<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(protected AuditLogger $auditLogger) {}

    /**
     * Display a listing of the users.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->hasPermission('users.view', $user->company_id), 403);

        $search = $request->query('search');

        $users = User::where('company_id', $user->company_id)
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->with(['roles', 'employeeProfile'])
            ->latest('id')
            ->paginate(15);

        return view('users.index', compact('users', 'search'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->hasPermission('users.create', $user->company_id), 403);

        $roles = Role::where('company_id', $user->company_id)->get();

        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->hasPermission('users.create', $user->company_id), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')],
            'password' => ['required', 'confirmed', Password::defaults()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')->where('company_id', $user->company_id)],
        ]);

        $newUser = DB::transaction(function () use ($validated, $user) {
            $newUser = User::create([
                'company_id' => $user->company_id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'preferred_locale' => 'ar',
            ]);

            if (! empty($validated['roles'])) {
                $syncData = [];
                foreach ($validated['roles'] as $roleId) {
                    $syncData[$roleId] = ['company_id' => $user->company_id];
                }
                $newUser->roles()->sync($syncData);
            }

            $this->auditLogger->log(
                action: 'user.created',
                auditable: $newUser,
                newValues: $newUser->only(['name', 'email']),
                metadata: ['roles_assigned' => $validated['roles'] ?? []],
                user: $user,
                company: $user->company_id
            );

            return $newUser;
        });

        return redirect()->route('users.index')->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء المستخدم بنجاح.' : 'User created successfully.');
    }

    /**
     * Display the specified user details.
     */
    public function show(User $user, Request $request): View
    {
        $authUser = $request->user();
        abort_unless($user->company_id === $authUser->company_id, 403);
        abort_unless($authUser->hasPermission('users.view', $authUser->company_id), 403);

        $user->load(['roles.permissions', 'employeeProfile.department', 'employeeProfile.jobTitle']);

        // Retrieve direct or role-inherited permissions
        $permissions = $user->roles->flatMap->permissions->unique('id')->values();

        return view('users.show', compact('user', 'permissions'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user, Request $request): View
    {
        $authUser = $request->user();
        abort_unless($user->company_id === $authUser->company_id, 403);
        abort_unless($authUser->hasPermission('users.update', $authUser->company_id), 403);

        $roles = Role::where('company_id', $authUser->company_id)->get();
        $userRoleIds = $user->roles()->pluck('roles.id')->toArray();

        return view('users.edit', compact('user', 'roles', 'userRoleIds'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $authUser = $request->user();
        abort_unless($user->company_id === $authUser->company_id, 403);
        abort_unless($authUser->hasPermission('users.update', $authUser->company_id), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')->where('company_id', $authUser->company_id)],
        ]);

        DB::transaction(function () use ($user, $validated, $authUser) {
            $oldValues = $user->only(['name', 'email']);

            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            if (! empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            if (isset($validated['roles'])) {
                $syncData = [];
                foreach ($validated['roles'] as $roleId) {
                    $syncData[$roleId] = ['company_id' => $authUser->company_id];
                }
                $user->roles()->sync($syncData);
            } else {
                $user->roles()->sync([]);
            }

            $this->auditLogger->log(
                action: 'user.updated',
                auditable: $user,
                oldValues: $oldValues,
                newValues: $user->only(['name', 'email']),
                metadata: ['roles_assigned' => $validated['roles'] ?? []],
                user: $authUser,
                company: $authUser->company_id
            );
        });

        return redirect()->route('users.index')->with('success', app()->getLocale() === 'ar' ? 'تم تحديث المستخدم بنجاح.' : 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user, Request $request): RedirectResponse
    {
        $authUser = $request->user();
        abort_unless($user->company_id === $authUser->company_id, 403);
        abort_unless($authUser->hasPermission('users.delete', $authUser->company_id), 403);

        // Protect from self-deletion
        if ($user->id === $authUser->id) {
            return redirect()->route('users.index')->with('error', app()->getLocale() === 'ar' ? 'لا يمكنك حذف حسابك الشخصي.' : 'You cannot delete your own account.');
        }

        DB::transaction(function () use ($user, $authUser) {
            $oldValues = $user->only(['name', 'email']);

            $user->roles()->sync([]);
            $user->delete();

            $this->auditLogger->log(
                action: 'user.deleted',
                auditable: $user,
                oldValues: $oldValues,
                user: $authUser,
                company: $authUser->company_id
            );
        });

        return redirect()->route('users.index')->with('success', app()->getLocale() === 'ar' ? 'تم حذف المستخدم بنجاح.' : 'User deleted successfully.');
    }
}
