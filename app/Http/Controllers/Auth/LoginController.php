<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(private OtpService $otp) {}

    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $user = Auth::user();

        if ($user->status === 'blocked' || $user->status === 'inactive') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Your account is not active. Please contact your administrator.',
            ]);
        }

        $request->session()->regenerate();

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        // Two-factor: send a code and gate the session until verified.
        if ($user->two_factor_enabled) {
            session(['otp.verified' => false]);
            $this->otp->generateAndSend($user);

            return redirect()->route('otp.show');
        }

        session(['otp.verified' => true]);

        return $this->afterAuthenticated();
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Decide where to send the user once credentials (and OTP) are cleared.
     */
    private function afterAuthenticated(): RedirectResponse
    {
        $user = Auth::user();
        $branches = $user->branches;

        if ($branches->count() > 1) {
            return redirect()->route('branch.select');
        }

        if ($branches->count() === 1) {
            $branch = $branches->first();
            session(['active_branch_id' => $branch->id, 'active_branch_name' => $branch->name]);
        }

        return redirect()->intended(route('dashboard'));
    }
}
