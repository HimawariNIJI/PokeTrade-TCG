<x-app-layout>

<section class="mx-auto max-w-[1100px] px-4 py-12 md:px-8 md:py-16">
    <a href="{{ route('trades.index') }}" class="text-xs font-semibold text-ink-500 hover:text-ink-900">← Back to trades</a>

    <h1 class="mt-4 font-display text-4xl font-black tracking-tight md:text-5xl">
        Propose a <span class="prism-text">trade</span>.
    </h1>
    <p class="mt-2 text-sm text-ink-700">Pick the cards you're offering and the cards you'd like in return.</p>

    <form method="POST" action="{{ route('trades.store') }}" class="mt-8 space-y-6">
        @csrf

        <div class="rounded-3xl border border-ink-200 bg-white p-6">
            <label class="block">
                <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Trade with (trainer email)</span>
                <input type="email" name="receiver_email" required
                       class="mt-1.5 w-full rounded-xl border-ink-200 focus:border-prism-violet focus:ring-prism-violet"
                       placeholder="trainer@kanto.com">
            </label>

            <label class="mt-4 block">
                <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Message (optional)</span>
                <textarea name="message" rows="3" class="mt-1.5 w-full rounded-xl border-ink-200 focus:border-prism-violet focus:ring-prism-violet"
                          placeholder="Hey! Wanna swap?"></textarea>
            </label>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-3xl border-2 border-dashed border-prism-violet/40 bg-prism-violet/5 p-6">
                <h2 class="font-display text-xl font-black">You offer</h2>
                <p class="mt-1 text-xs text-ink-500">Pick cards from your collection.</p>
                <div class="mt-4 grid grid-cols-3 gap-2">
                    @for($i = 0; $i < 3; $i++)
                        <div class="aspect-[245/342] rounded-xl border-2 border-dashed border-ink-300 bg-white/50 flex items-center justify-center text-2xl text-ink-300">+</div>
                    @endfor
                </div>
                <p class="mt-3 text-[11px] text-ink-500">TODO(team-backend): card-picker modal that lists cards the user owns.</p>
            </div>

            <div class="rounded-3xl border-2 border-dashed border-prism-mint/40 bg-prism-mint/5 p-6">
                <h2 class="font-display text-xl font-black">You request</h2>
                <p class="mt-1 text-xs text-ink-500">Pick cards from their collection.</p>
                <div class="mt-4 grid grid-cols-3 gap-2">
                    @for($i = 0; $i < 3; $i++)
                        <div class="aspect-[245/342] rounded-xl border-2 border-dashed border-ink-300 bg-white/50 flex items-center justify-center text-2xl text-ink-300">+</div>
                    @endfor
                </div>
                <p class="mt-3 text-[11px] text-ink-500">TODO(team-backend): autocomplete/search across the catalog.</p>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('trades.index') }}" class="rounded-full border border-ink-200 px-5 py-2.5 text-sm font-bold text-ink-700 hover:border-ink-900">Cancel</a>
            <x-prism-button type="submit" size="md">Send proposal</x-prism-button>
        </div>
    </form>
</section>

</x-app-layout>
