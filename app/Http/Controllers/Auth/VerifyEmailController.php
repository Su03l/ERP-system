<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifyEmailController extends Controller
{
    public function show(Request $request): RedirectResponse|View
    {
        return $request->user()?->hasVerifiedEmail()
            ? redirect()->intended(route('dashboard'))
            : view('auth.verify-email');
    }

    public function resend(Request $request): RedirectResponse
    {
        return back()->with('success', app()->getLocale() === 'ar'
            ? 'تم إرسال رابط تحقق جديد إلى بريدك الإلكتروني!'
            : 'A new verification link has been sent to your email!');
    }
}
