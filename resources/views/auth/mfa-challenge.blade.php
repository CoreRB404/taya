<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Enter the six-digit verification code sent to your email address. The code expires shortly.
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('mfa.verify') }}">
        @csrf
        <div>
            <x-input-label for="code" value="Verification code" />
            <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>Verify</x-primary-button>
        </div>
    </form>

    <form method="POST" action="{{ route('mfa.resend') }}" class="mt-4">
        @csrf
        <button type="submit" class="text-sm text-gray-600 underline hover:text-gray-900">Send a new code</button>
    </form>
</x-guest-layout>
