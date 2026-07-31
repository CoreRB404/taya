<x-guest-layout>
    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-5 px-4 py-3 rounded-xl bg-green-500/10 border border-green-500/20 text-green-300 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="text-center mb-8">
        <h2 style="color: #ffffff; font-size: 1.25rem; font-weight: 700; letter-spacing: -0.025em;">Welcome back</h2>
        <p style="color: rgba(255,255,255,0.55); font-size: 0.875rem; margin-top: 0.25rem;">Sign in to access your dashboard</p>
    </div>

    {{-- Login form follows... --}}

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-5">
            <label for="email" class="block mb-2" style="color: rgba(255,255,255,0.85); font-size: 0.875rem; font-weight: 500;">Email Address</label>
            <div style="display: flex; align-items: center; gap: 0.75rem; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 0.75rem; padding: 0 1rem; transition: all 0.25s ease;" class="input-wrapper">
                <svg class="w-5 h-5" style="color: rgba(255,255,255,0.4); flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    style="background: transparent; border: none; outline: none; color: #fff; width: 100%; padding: 0.75rem 0; font-size: 0.875rem;">
            </div>
            @error('email')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-5">
            <label for="password" class="block mb-2" style="color: rgba(255,255,255,0.85); font-size: 0.875rem; font-weight: 500;">Password</label>
            <div style="display: flex; align-items: center; gap: 0.75rem; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 0.75rem; padding: 0 1rem; transition: all 0.25s ease;" class="input-wrapper">
                <svg class="w-5 h-5" style="color: rgba(255,255,255,0.4); flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    style="background: transparent; border: none; outline: none; color: #fff; width: 100%; padding: 0.75rem 0; font-size: 0.875rem;">
                <button type="button" id="togglePassword" onclick="togglePasswordVisibility()" style="background: none; border: none; cursor: pointer; padding: 0.25rem; flex-shrink: 0; color: rgba(255,255,255,0.4); transition: color 0.2s;" onmouseover="this.style.color='rgba(255,255,255,0.7)'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">
                    <!-- Eye open icon -->
                    <svg id="eyeOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: block;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <!-- Eye closed icon -->
                    <svg id="eyeClosed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l18 18"/>
                    </svg>
                </button>
            </div>
            <script>
                function togglePasswordVisibility() {
                    const input = document.getElementById('password');
                    const eyeOpen = document.getElementById('eyeOpen');
                    const eyeClosed = document.getElementById('eyeClosed');
                    if (input.type === 'password') {
                        input.type = 'text';
                        eyeOpen.style.display = 'none';
                        eyeClosed.style.display = 'block';
                    } else {
                        input.type = 'password';
                        eyeOpen.style.display = 'block';
                        eyeClosed.style.display = 'none';
                    }
                }
            </script>
            @error('password')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between mb-6">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" name="remember"
                    class="w-4 h-4 rounded border-white/20 bg-white/5 text-blue-500 focus:ring-blue-500/30 focus:ring-offset-0 transition">
                <span class="ms-2 text-sm remember-text" style="color: rgba(255,255,255,0.6);">Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a style="color: #60a5fa; font-size: 0.875rem;" href="{{ route('password.request') }}">
                    Forgot password?
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-login w-full py-3 rounded-xl text-sm tracking-wide cursor-pointer">
            Sign In
        </button>
    </form>

    @push('below-login')
        <div class="relative z-10 mt-6 text-center">
            <a href="{{ route('tracking.lookup') }}" role="button" class="btn-back">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back to public interface
            </a>
        </div>
    @endpush

</x-guest-layout>
