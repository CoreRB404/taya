<?php

return [
    'registration_enabled' => env('REGISTRATION_ENABLED', false),
    'mfa' => [
        'required' => env('MFA_REQUIRED', app()->environment('production')),
        'code_ttl_minutes' => (int) env('MFA_CODE_TTL_MINUTES', 10),
        'resend_seconds' => (int) env('MFA_RESEND_SECONDS', 60),
        'session_ttl_minutes' => (int) env('MFA_SESSION_TTL_MINUTES', 720),
    ],
    'content_security_policy' => env(
        'CONTENT_SECURITY_POLICY',
        "default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; object-src 'none'; img-src 'self' data:; font-src 'self' https://fonts.gstatic.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; script-src 'self' 'unsafe-inline'"
    ),
];
