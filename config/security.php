<?php

return [
    'registration_enabled' => env('REGISTRATION_ENABLED', false),
    'documents' => [
        // Render sets this to "supabase". Local development remains usable
        // without cloud credentials, while production fails closed to durable
        // storage if the variable is accidentally omitted.
        'disk' => env(
            'DOCUMENTS_DISK',
            env('APP_ENV', 'production') === 'production' ? 'supabase' : 'local'
        ),
        'allowed_disks' => ['local', 'supabase'],
    ],
    'content_security_policy' => env(
        'CONTENT_SECURITY_POLICY',
        "default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; object-src 'none'; img-src 'self' data:; font-src 'self' https://fonts.gstatic.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; script-src 'self' 'unsafe-inline'"
    ),
];
