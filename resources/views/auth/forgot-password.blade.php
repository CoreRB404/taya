<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-xl font-bold text-white tracking-tight">Reset Password</h2>
        <p class="text-sm text-white/40 mt-2 leading-relaxed">Forgot your password? No problem. Enter your email and we'll send you a reset link.</p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-5 px-4 py-3 rounded-xl bg-green-500/10 border border-green-500/20 text-green-300 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-5">
            <label for="email" class="block text-sm font-medium text-white/70 mb-2">Email Address</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="input-dark w-full pl-11 pr-4 py-3 rounded-xl text-sm placeholder-white/35"
                    placeholder="you@example.com">
            </div>
            @error('email')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-login w-full py-3 rounded-xl text-sm tracking-wide cursor-pointer">
            Email Password Reset Link
        </button>

        <div class="mt-5 text-center">
            <a href="{{ route('login') }}" class="text-sm text-blue-400 hover:text-blue-300 transition-colors">
                ← Back to Sign In
            </a>
        </div>
    </form>
</x-guest-layout>
