<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OtpService
{
    private const SESSION_HASH = 'otp.hash';

    private const SESSION_EXPIRES = 'otp.expires';

    private const TTL_MINUTES = 10;

    /**
     * Generate a fresh 6 digit code, store it (hashed) in the session and
     * deliver it to the user. With the log mail driver it is written to the
     * Laravel log so it can be read during local development.
     */
    public function generateAndSend(User $user): void
    {
        $code = (string) random_int(100000, 999999);

        session([
            self::SESSION_HASH => Hash::make($code),
            self::SESSION_EXPIRES => now()->addMinutes(self::TTL_MINUTES)->timestamp,
        ]);

        Mail::raw(
            "Your {$this->appName()} verification code is: {$code}\n\nThis code expires in ".self::TTL_MINUTES.' minutes. If you did not try to sign in, please contact your administrator.',
            function ($message) use ($user) {
                $message->to($user->email)->subject($this->appName().' Login Verification Code');
            }
        );
    }

    public function verify(?string $code): bool
    {
        $hash = session(self::SESSION_HASH);
        $expires = session(self::SESSION_EXPIRES);

        if (! $hash || ! $expires || now()->timestamp > $expires) {
            return false;
        }

        if (! $code || ! Hash::check($code, $hash)) {
            return false;
        }

        $this->clear();

        return true;
    }

    public function clear(): void
    {
        session()->forget([self::SESSION_HASH, self::SESSION_EXPIRES]);
    }

    public function maskEmail(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email), 2, '');
        $visible = Str::substr($name, 0, 2);

        return $visible.str_repeat('*', max(strlen($name) - 2, 1)).'@'.$domain;
    }

    private function appName(): string
    {
        return config('app.name', 'PharmaCore');
    }
}
