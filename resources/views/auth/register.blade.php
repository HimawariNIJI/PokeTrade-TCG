<x-guest-layout>
    <h1 class="font-display text-4xl font-black tracking-tight">
        Start your <span class="prism-text">collection</span>.
    </h1>
    <p class="mt-2 text-sm text-ink-700">Create an account to wishlist, bid, and trade.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
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

        <x-prism-button type="submit" size="lg" class="w-full" :disabled="$isSubmitting ?? false" x-bind:disabled="isSubmitting">
            <span x-show="!isSubmitting">Create my account →</span>
            <span x-show="isSubmitting" class="inline-flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Creating account...
            </span>
        </x-prism-button>

        <p class="text-center text-sm text-ink-500">
            Already a trainer?
            <a href="{{ route('login') }}" class="font-bold text-ink-900 hover:text-prism-violet">Log in →</a>
        </p>
    </form>
</x-guest-layout>
