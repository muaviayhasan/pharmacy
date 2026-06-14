<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the authenticated app behind the post-login steps:
 *   1. Two-factor OTP verification (when enabled for the user).
 *   2. Active branch selection (when the user belongs to multiple branches).
 */
class EnsurePostLoginComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($user->two_factor_enabled && session('otp.verified') !== true) {
            return redirect()->route('otp.show');
        }

        if (! session('active_branch_id') && $user->branches()->count() > 1) {
            return redirect()->route('branch.select');
        }

        return $next($request);
    }
}
