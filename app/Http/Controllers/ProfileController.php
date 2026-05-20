<?php

namespace App\Http\Controllers;

use App\Actions\CreateApiToken;
use App\Actions\RevokeApiToken;
use App\Actions\RevokeUserSession;
use App\Http\Requests\StoreApiTokenRequest;
use App\Models\CompanyApiToken;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserSession;
use App\Services\UserSessionQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request, UserSessionQuery $sessionQuery): View
    {
        $user = $request->user();

        // Retrieve active sessions using UserSessionQuery
        $sessions = $sessionQuery->rows($user, [
            'user_id' => $user->id,
            'revoked' => false,
        ]);

        // Retrieve personal API tokens for this user
        $tokens = CompanyApiToken::query()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->latest('id')
            ->get();

        // Retrieve all permissions in the system for granular abilities assignment
        $permissions = Permission::all();

        return view('profile.show', compact('user', 'sessions', 'tokens', 'permissions'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'preferred_locale' => ['required', Rule::in(['ar', 'en'])],
        ]);

        $user->update($validated);

        return redirect()->route('profile.show')->with('success', app()->getLocale() === 'ar' ? 'تم تحديث الملف الشخصي بنجاح.' : 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.show')->with('success', app()->getLocale() === 'ar' ? 'تم تغيير كلمة المرور بنجاح.' : 'Password changed successfully.');
    }

    public function revokeSession(Request $request, UserSession $userSession, RevokeUserSession $action): RedirectResponse
    {
        $action->handle($userSession, $request->user());

        return redirect()->route('profile.show')->with('success', app()->getLocale() === 'ar' ? 'تم إنهاء الجلسة بنجاح.' : 'Session terminated successfully.');
    }

    public function createToken(StoreApiTokenRequest $request, CreateApiToken $action): RedirectResponse
    {
        $result = $action->handle($request->validated(), $request->user());

        return redirect()->route('profile.show')->with([
            'success' => app()->getLocale() === 'ar' ? 'تم إنشاء رمز API بنجاح.' : 'API token created successfully.',
            'plain_text_token' => $result['plain_text_token'],
        ]);
    }

    public function revokeToken(Request $request, CompanyApiToken $companyApiToken, RevokeApiToken $action): RedirectResponse
    {
        $action->handle($companyApiToken, $request->user());

        return redirect()->route('profile.show')->with('success', app()->getLocale() === 'ar' ? 'تم إلغاء رمز API بنجاح.' : 'API token revoked successfully.');
    }
}
