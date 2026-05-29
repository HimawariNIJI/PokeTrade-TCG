<x-guest-layout>
    <h1 class="font-display text-4xl font-black tracking-tight">
        Set a new <span class="prism-text">password</span>
    </h1>
    <p class="mt-2 text-sm text-ink-700">Choose a strong password for your account.</p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    @if ($errors->any())
        <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 p-4">
            @foreach ($errors->all() as $error)
                <p class="text-sm text-rose-600">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('otp.reset') }}" class="mt-8 space-y-5">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">

        <label class="block">
            <span class="text-xs font-bold uppercase tracking-widest text-ink-700">New password</span>
            <input type="password" name="password" required autocomplete="new-password"
                   class="mt-1.5 w-full rounded-xl border border-ink-200 bg-white px-4 py-3 text-ink-900 placeholder-ink-400 focus:border-prism-violet focus:ring-2 focus:ring-prism-violet/20">
            @error('password') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
        </label>

        <label class="block">
            <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Confirm password</span>
            <input type="password" name="password_confirmation" required autocomplete="new-password"
                   class="mt-1.5 w-full rounded-xl border border-ink-200 bg-white px-4 py-3 text-ink-900 placeholder-ink-400 focus:border-prism-violet focus:ring-2 focus:ring-prism-violet/20">
            @error('password_confirmation') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
        </label>

        <x-prism-button type="submit" size="lg" class="w-full">
            Reset password
        </x-prism-button>

        <p class="text-center text-sm">
            <a href="{{ route('login') }}" class="font-semibold text-ink-700 hover:text-prism-violet">← Back to login</a>
        </p>
    </form>
</x-guest-layout>
