<x-guest-layout>
    <h1 class="font-display text-4xl font-black tracking-tight">
        Forgot your <span class="prism-text">password</span>?
    </h1>
    <p class="mt-2 text-sm text-ink-700">Drop your email below and we'll send you a reset link.</p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
        @csrf
        <label class="block">
            <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Email</span>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="mt-1.5 w-full rounded-xl border-ink-200 focus:border-prism-violet focus:ring-prism-violet">
            @error('email') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
        </label>

        <x-prism-button type="submit" size="lg" class="w-full">
            Email me the reset link
        </x-prism-button>

        <p class="text-center text-sm">
            <a href="{{ route('login') }}" class="font-semibold text-ink-700 hover:text-prism-violet">← Back to login</a>
        </p>
    </form>
</x-guest-layout>
