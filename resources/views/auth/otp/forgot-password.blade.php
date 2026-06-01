<x-guest-layout>
    <h1 class="font-display text-4xl font-black tracking-tight">
        Forgot your <span class="prism-text">password</span>?
    </h1>
    <p class="mt-2 text-sm text-ink-700">Submit your email and we'll send you a verification code.</p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    @if (session('error'))
        <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 p-4">
            <p class="text-sm text-rose-600 font-medium">{{ session('error') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 p-4">
            @foreach ($errors->all() as $error)
                <p class="text-sm text-rose-600">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('otp.send') }}" class="mt-8 space-y-5">
        @csrf
        <label class="block">
            <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Email</span>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="mt-1.5 w-full rounded-xl border border-ink-200 bg-white px-4 py-3 text-ink-900 placeholder-ink-400 focus:border-prism-violet focus:ring-2 focus:ring-prism-violet/20">
            @error('email') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
        </label>

        <x-prism-button type="submit" size="lg" class="w-full">
            Send verification code
        </x-prism-button>

        <p class="text-center text-sm">
            <a href="{{ route('login') }}" class="font-semibold text-ink-700 hover:text-prism-violet">← Back to login</a>
        </p>
    </form>
</x-guest-layout>