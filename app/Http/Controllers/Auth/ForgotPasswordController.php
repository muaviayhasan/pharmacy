<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function show()
    {
        return view('auth.forgot-password');
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        // Always report success to avoid leaking which emails are registered.
        return back()->with('status', __($status) === __(Password::RESET_LINK_SENT)
            ? 'We have emailed your password reset link.'
            : 'If that email is registered, a reset link has been sent.');
    }
}
