<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', app()->getLocale() === 'ar' ? 'تم تسجيل الدخول بنجاح!' : 'Logged in successfully!');
        }

        throw ValidationException::withMessages([
            'email' => [app()->getLocale() === 'ar' ? 'بيانات الاعتماد المدخلة غير صحيحة.' : 'The provided credentials do not match our records.'],
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تسجيل الخروج بنجاح.' : 'Logged out successfully.');
    }
}
