<x-guest-layout>
    <h1 class="font-display text-4xl font-black tracking-tight">
        Set a new <span class="prism-text">password</span>.
    </h1>

    <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <label class="block">
            <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Email</span>
            <input type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                   class="mt-1.5 w-full rounded-xl border-ink-200 focus:border-prism-violet focus:ring-prism-violet">
            @error('email') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
        </label>

        <label class="block">
            <span class="text-xs font-bold uppercase tracking-widest text-ink-700">New password</span>
            <input type="password" name="password" required autocomplete="new-password"
                   class="mt-1.5 w-full rounded-xl border-ink-200 focus:border-prism-violet focus:ring-prism-violet">
            @error('password') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
        </label>

        <label class="block">
            <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Confirm</span>
            <input type="password" name="password_confirmation" required autocomplete="new-password"
                   class="mt-1.5 w-full rounded-xl border-ink-200 focus:border-prism-violet focus:ring-prism-violet">
        </label>

        <x-prism-button type="submit" size="lg" class="w-full">
            Reset password
        </x-prism-button>
    </form>
</x-guest-layout>
