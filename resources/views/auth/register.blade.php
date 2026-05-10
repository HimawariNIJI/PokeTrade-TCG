<x-guest-layout>
    <h1 class="font-display text-4xl font-black tracking-tight">
        Start your <span class="prism-text">collection</span>.
    </h1>
    <p class="mt-2 text-sm text-ink-700">Create an account to wishlist, bid, and trade.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
        @csrf

        <label class="block">
            <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Trainer name</span>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="mt-1.5 w-full rounded-xl border-ink-200 focus:border-prism-violet focus:ring-prism-violet">
            @error('name') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
        </label>

        <label class="block">
            <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Email</span>
            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   class="mt-1.5 w-full rounded-xl border-ink-200 focus:border-prism-violet focus:ring-prism-violet">
            @error('email') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
        </label>

        <div class="grid gap-4 md:grid-cols-2">
            <label class="block">
                <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Password</span>
                <input type="password" name="password" required autocomplete="new-password"
                       class="mt-1.5 w-full rounded-xl border-ink-200 focus:border-prism-violet focus:ring-prism-violet">
                @error('password') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
            </label>

            <label class="block">
                <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Confirm</span>
                <input type="password" name="password_confirmation" required autocomplete="new-password"
                       class="mt-1.5 w-full rounded-xl border-ink-200 focus:border-prism-violet focus:ring-prism-violet">
            </label>
        </div>

        <x-prism-button type="submit" size="lg" class="w-full">
            Create my account →
        </x-prism-button>

        <p class="text-center text-sm text-ink-500">
            Already a trainer?
            <a href="{{ route('login') }}" class="font-bold text-ink-900 hover:text-prism-violet">Log in →</a>
        </p>
    </form>
</x-guest-layout>
