<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OtpController extends Controller
{
    public function __construct(private OtpService $otp) {}

    public function show(Request $request)
    {
        if (! $request->user()->two_factor_enabled || session('otp.verified') === true) {
            return redirect()->route('dashboard');
        }

        return view('auth.otp', [
            'maskedEmail' => $this->otp->maskEmail($request->user()->email),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'digit' => ['required', 'array', 'size:6'],
            'digit.*' => ['required', 'string'],
        ]);

        $code = implode('', $request->input('digit'));

        if (! $this->otp->verify($code)) {
            throw ValidationException::withMessages([
                'digit' => 'The verification code is invalid or has expired.',
            ]);
        }

        session(['otp.verified' => true]);

        $branches = $request->user()->branches;
        if ($branches->count() > 1) {
            return redirect()->route('branch.select');
        }
        if ($branches->count() === 1) {
            $branch = $branches->first();
            session(['active_branch_id' => $branch->id, 'active_branch_name' => $branch->name]);
        }

        return redirect()->intended(route('dashboard'));
    }

    public function resend(Request $request): RedirectResponse
    {
        $this->otp->generateAndSend($request->user());

        return back()->with('status', 'A new verification code has been sent.');
    }
}
