<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'TAYA') }} - Sign In</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .login-bg {
                background: linear-gradient(135deg, #0a1128 0%, #121e42 40%, #1c2e63 70%, #2a4185 100%);
                min-height: 100vh;
                position: relative;
                overflow: hidden;
            }

            /* Animated floating orbs for depth */
            .login-bg::before {
                content: '';
                position: absolute;
                top: -20%;
                right: -10%;
                width: 600px;
                height: 600px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%);
                animation: float-orb 8s ease-in-out infinite;
            }

            .login-bg::after {
                content: '';
                position: absolute;
                bottom: -15%;
                left: -10%;
                width: 500px;
                height: 500px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(96, 165, 250, 0.1) 0%, transparent 70%);
                animation: float-orb 10s ease-in-out infinite reverse;
            }

            @keyframes float-orb {
                0%, 100% { transform: translate(0, 0) scale(1); }
                33% { transform: translate(30px, -20px) scale(1.05); }
                66% { transform: translate(-20px, 15px) scale(0.95); }
            }

            /* Grid pattern overlay */
            .grid-pattern {
                position: absolute;
                inset: 0;
                background-image:
                    linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
                background-size: 60px 60px;
                pointer-events: none;
            }

            .login-card {
                background: rgba(10, 17, 40, 0.85);
                backdrop-filter: blur(24px);
                -webkit-backdrop-filter: blur(24px);
                border: 1px solid rgba(255, 255, 255, 0.12);
                box-shadow:
                    0 25px 50px -12px rgba(0, 0, 0, 0.5),
                    0 0 0 1px rgba(255, 255, 255, 0.05) inset,
                    0 1px 0 rgba(255, 255, 255, 0.1) inset;
                color: #fff;
            }

            .login-card:hover {
                border-color: rgba(59, 130, 246, 0.2);
                box-shadow:
                    0 25px 50px -12px rgba(0, 0, 0, 0.5),
                    0 0 0 1px rgba(59, 130, 246, 0.1) inset,
                    0 1px 0 rgba(255, 255, 255, 0.1) inset,
                    0 0 40px rgba(59, 130, 246, 0.05);
            }

            .input-dark {
                background: rgba(255, 255, 255, 0.08);
                border: 1px solid rgba(255, 255, 255, 0.15);
                color: #ffffff !important;
                transition: all 0.25s ease;
            }

            .input-dark::placeholder {
                color: rgba(255, 255, 255, 0.35);
            }

            .input-dark:focus {
                background: rgba(255, 255, 255, 0.08);
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15), 0 0 20px rgba(59, 130, 246, 0.1);
                outline: none;
            }

            .btn-login {
                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                color: #fff;
                font-weight: 600;
                border: none;
                position: relative;
                overflow: hidden;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
            }

            .btn-login:hover {
                transform: translateY(-1px);
                box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
            }

            .btn-login:active {
                transform: translateY(0) scale(0.98);
            }

            .btn-login::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
                transition: left 0.5s ease;
            }

            .btn-login:hover::before {
                left: 100%;
            }

            /* Fade-in animation for the card */
            .animate-card-in {
                animation: card-in 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }

            @keyframes card-in {
                from {
                    opacity: 0;
                    transform: translateY(20px) scale(0.98);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            /* Staggered label animation */
            .stagger-1 { animation-delay: 0.1s; }
            .stagger-2 { animation-delay: 0.2s; }
            .stagger-3 { animation-delay: 0.3s; }

            /* Force text colors on dark background — override Tailwind base */
            .login-bg { color: #ffffff; }
            .login-card label,
            .login-card .form-label { color: rgba(255, 255, 255, 0.85) !important; }
            .login-card h2 { color: #ffffff !important; }
            .login-card p { color: rgba(255, 255, 255, 0.6) !important; }
            .login-card .remember-text { color: rgba(255, 255, 255, 0.6) !important; }
            .login-card a { color: #60a5fa !important; }
            .login-card a:hover { color: #93bbfd !important; }

            /* Input wrapper focus glow */
            .input-wrapper:focus-within {
                border-color: #3b82f6 !important;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15), 0 0 20px rgba(59, 130, 246, 0.1);
            }

            /* Back / secondary button used below the login card */
            .btn-back {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 11px 22px;
                border-radius: 100px;
                background: rgba(255,255,255,0.04);
                border: 1px solid #2a3363;
                color: #a2ade0;
                font-size: 13.5px;
                font-weight: 500;
                font-family: 'IBM Plex Sans', sans-serif;
                text-decoration: none;
                transition: background .15s ease, border-color .15s ease, color .15s ease, transform .15s ease;
            }
            .btn-back:hover {
                background: rgba(255,255,255,0.07);
                border-color: rgba(34,211,238,0.4);
                color: #f5f7fa;
                transform: translateY(-1px);
            }
            .btn-back svg {
                transition: transform .15s ease;
            }
            .btn-back:hover svg {
                transform: translateX(-2px);
            }

            /* Public page staff-login button */
            .public-staff-button {
                color: #93c5fd;
                background: transparent;
                border: none;
                font-weight: 600;
                padding: 0.5rem 0.75rem;
                transition: color 0.15s ease;
            }
            .public-staff-button:hover { color: #c7e6ff; }

            /* Search button refinement for public lookup */
            .btn-search {
                background: linear-gradient(90deg, #06b6d4 0%, #3b82f6 100%);
                color: #fff;
                font-weight: 700;
                border-radius: 0.75rem;
                padding: 0.9rem 1rem;
                box-shadow: 0 12px 30px rgba(59,130,246,0.14);
                transition: transform 0.15s ease, box-shadow 0.15s ease;
            }
            .btn-search:hover { transform: translateY(-2px); box-shadow: 0 18px 36px rgba(59,130,246,0.18); }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="login-bg flex flex-col items-center justify-center px-4 sm:px-6">
            <div class="grid-pattern"></div>

            <!-- Logo & Branding -->
            <div class="relative z-10 mb-8 text-center animate-card-in">
                <div class="inline-flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                        </svg>
                    </div>
                    <span class="text-3xl font-bold tracking-tight text-white">TAYA</span>
                </div>
                <p style="color: rgba(255,255,255,0.6); font-size: 0.875rem; letter-spacing: 0.025em;">Detainee Rights & Overstay Alert System</p>
            </div>

            <!-- Login Card -->
            <div class="w-full sm:max-w-md login-card rounded-2xl p-8 relative z-10 animate-card-in stagger-1 transition-all duration-300">
                {{ $slot }}
            </div>

            @stack('below-login')

            <!-- Footer -->
            <div class="relative z-10 mt-8 text-center animate-card-in stagger-3">
                <p style="color: rgba(255,255,255,0.45); font-size: 0.75rem;">&copy; {{ date('Y') }} TAYA System. All rights reserved.</p>
            </div>
        </div>
    </body>
</html>
