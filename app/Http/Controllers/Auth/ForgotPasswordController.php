<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function show(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'string', 'email']]);

        return back()->with('success', app()->getLocale() === 'ar'
            ? 'تم إرسال رابط استعادة كلمة المرور إلى بريدك الإلكتروني!'
            : 'We have emailed your password reset link!');
    }
}
