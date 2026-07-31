<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMfaVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->requiresMfa()) {
            return $next($request);
        }

        $verifiedAt = (int) $request->session()->get('mfa_verified_at', 0);
        $ttl = config('security.mfa.session_ttl_minutes') * 60;

        if ($verifiedAt > 0 && (time() - $verifiedAt) < $ttl) {
            return $next($request);
        }

        return redirect()->route('mfa.challenge');
    }
}
