<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    public function show(Request $request, ?string $token = null): View
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        return redirect()->route('login')->with('success', app()->getLocale() === 'ar'
            ? 'تم إعادة تعيين كلمة المرور بنجاح! يمكنك الآن تسجيل الدخول.'
            : 'Your password has been reset! You can now log in.');
    }
}
