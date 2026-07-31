<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\MfaCodeNotification;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MfaChallengeController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if (! $request->user()->requiresMfa()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        return view('auth.mfa-challenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        if (! $request->user()->requiresMfa()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $validated = $request->validate(['code' => ['required', 'digits:6']]);
        $user = $request->user();
        $key = 'mfa-verify:'.$user->id.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['code' => 'Too many attempts. Try again later.']);
        }

        if (! $user->mfa_code_hash || ! $user->mfa_expires_at?->isFuture() || ! Hash::check($validated['code'], $user->mfa_code_hash)) {
            RateLimiter::hit($key, 300);
            throw ValidationException::withMessages(['code' => 'The verification code is invalid or expired.']);
        }

        RateLimiter::clear($key);
        $user->forceFill(['mfa_code_hash' => null, 'mfa_expires_at' => null])->save();
        $request->session()->regenerate();
        $request->session()->put('mfa_verified_at', time());
        AuditService::log('mfa_verified', 'Multi-factor authentication challenge completed.');

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user->requiresMfa()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }
        $key = 'mfa-send:'.$user->id.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 1)) {
            return back()->withErrors(['code' => 'Please wait before requesting another code.']);
        }

        RateLimiter::hit($key, config('security.mfa.resend_seconds'));
        try {
            $this->sendCode($user);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['code' => 'We could not send a new code. Contact an administrator.']);
        }

        return back()->with('status', 'A new verification code was sent.');
    }

    public static function sendCode($user): void
    {
        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'mfa_code_hash' => Hash::make($code),
            'mfa_expires_at' => now()->addMinutes(config('security.mfa.code_ttl_minutes')),
            'mfa_last_sent_at' => now(),
        ])->save();

        $user->notify(new MfaCodeNotification($code));
    }
}
