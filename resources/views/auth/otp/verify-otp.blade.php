<x-guest-layout>
    <h1 class="font-display text-4xl font-black tracking-tight">
        Enter the <span class="prism-text">verification code</span>
    </h1>
    <p class="mt-2 text-sm text-ink-700">We sent a 6-digit code to <strong>{{ $email }}</strong></p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    @if ($errors->any())
        <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 p-4">
            @foreach ($errors->all() as $error)
                <p class="text-sm text-rose-600">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('otp.verify') }}" class="mt-8 space-y-5" x-data="otpTimer()">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">

        <label class="block">
            <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Verification Code</span>
            <input type="text" name="otp" placeholder="000000" maxlength="6" inputmode="numeric" 
                   required autofocus
                   class="mt-1.5 w-full rounded-xl border border-ink-200 bg-white px-4 py-4 text-center text-2xl tracking-widest font-mono text-ink-900 placeholder-ink-400 focus:border-prism-violet focus:ring-2 focus:ring-prism-violet/20">
            @error('otp') <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span> @enderror
        </label>

        {{-- Timer --}}
        <div class="rounded-lg bg-prism-violet/10 p-4 text-center">
            <p class="text-xs font-bold uppercase tracking-widest text-ink-700">Time remaining</p>
            <p class="mt-2 font-mono text-2xl font-bold" :class="timeLeft <= 60 ? 'text-rose-600' : 'text-prism-violet'">
                <span x-text="formatTime"></span>
            </p>
            <p class="mt-2 text-xs text-ink-600" x-show="timeLeft <= 60">Hurry! Your code expires soon.</p>
        </div>

        <x-prism-button type="submit" size="lg" class="w-full" x-bind:disabled="timeLeft <= 0">
            <span x-show="timeLeft > 0">Verify code</span>
            <span x-show="timeLeft <= 0">Code expired - request a new one</span>
        </x-prism-button>

        <div class="text-center">
            <p class="text-sm text-ink-600">
                Didn't receive the code?
                <a href="{{ route('otp.forgot-password') }}" class="font-semibold text-prism-violet hover:underline">Request new code</a>
            </p>
        </div>
    </form>

    @once
        <script>
            function otpTimer() {
                return {
                    timeLeft: 600, // 10 minutes in seconds
                    
                    init() {
                        this.startTimer();
                    },
                    
                    startTimer() {
                        setInterval(() => {
                            if (this.timeLeft > 0) {
                                this.timeLeft--;
                            }
                        }, 1000);
                    },
                    
                    get formatTime() {
                        const minutes = Math.floor(this.timeLeft / 60);
                        const seconds = this.timeLeft % 60;
                        return `${minutes}:${seconds.toString().padStart(2, '0')}`;
                    }
                };
            }
        </script>
    @endonce
</x-guest-layout>
