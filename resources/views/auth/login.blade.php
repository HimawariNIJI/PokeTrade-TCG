<x-guest-layout>
    <h1 class="font-display text-4xl font-black tracking-tight">
        Welcome back, <span class="prism-text">trainer</span>.
    </h1>
    <p class="mt-2 text-sm text-ink-700">Log in to bid, trade, and pull packs.</p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <label class="block">
            <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Email</span>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="mt-1.5 w-full rounded-xl border-ink-200 focus:border-prism-violet focus:ring-prism-violet">
            @error('email') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
        </label>

        <label class="block">
            <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Password</span>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="mt-1.5 w-full rounded-xl border-ink-200 focus:border-prism-violet focus:ring-prism-violet">
            @error('password') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
        </label>

        <div class="flex items-center justify-between">
            <label class="inline-flex cursor-pointer items-center gap-2">
                <input type="checkbox" name="remember" class="rounded border-ink-300 text-prism-violet focus:ring-prism-violet">
                <span class="text-sm text-ink-700">Remember me</span>
            </label>
            @if (Route::has('otp.forgot-password'))
                <a class="text-sm font-semibold text-ink-700 hover:text-prism-violet" href="{{ route('otp.forgot-password') }}">
                    Forgot password?
                </a>
            @endif
        </div>

        <x-prism-button type="submit" size="lg" class="w-full">
            Log in →
        </x-prism-button>

        {{-- Google OAuth placeholder --}}
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-ink-200"></div></div>
            <div class="relative flex justify-center"><span class="bg-white px-3 text-[11px] font-bold uppercase tracking-widest text-ink-500">or</span></div>
        </div>
        <a href="/auth/google" class="flex w-full items-center justify-center gap-2 rounded-full border border-ink-200 bg-white px-5 py-3 text-sm font-bold text-ink-700 hover:border-prism-violet">
            <svg class="h-4 w-4" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4C12.955 4 4 12.955 4 24s8.955 20 20 20s20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/><path fill="#FF3D00" d="m6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4C16.318 4 9.656 8.337 6.306 14.691z"/><path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0 1 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/><path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002l6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/></svg>
            Continue with Google
            <span class="ml-1 text-[10px] uppercase tracking-widest text-ink-500"></span>
        </a>

        <p class="text-center text-sm text-ink-500">
            New to PokeTrade?
            <a href="{{ route('register') }}" class="font-bold text-ink-900 hover:text-prism-violet">Create an account →</a>
        </p>
    </form>
</x-guest-layout>
