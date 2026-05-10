<x-app-layout>

<section class="relative overflow-hidden bg-ink-900 text-white">
    <div class="absolute inset-0 -z-10 opacity-30" style="background: radial-gradient(closest-side, rgba(255, 106, 213, 0.4), transparent 70%);"></div>

    <div class="mx-auto max-w-[1400px] px-4 py-16 md:px-8 md:py-24">
        <div class="text-center">
            <span class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest backdrop-blur">
                Pack opened
            </span>
            <h1 class="mt-4 font-display text-5xl font-black md:text-7xl">
                Your <span class="prism-text">5 cards</span>.
            </h1>
            <p class="mt-3 text-white/70">Tap a card for full details.</p>
        </div>

        <div class="mx-auto mt-14 grid max-w-[1100px] grid-cols-2 gap-5 md:grid-cols-5">
            @foreach($pulls as $card)
                <a href="{{ route('cards.show', $card) }}" class="block">
                    <div class="card-surface relative shadow-2xl">
                        @if($card->image_large)
                            <img src="{{ $card->image_large }}" alt="{{ $card->name }}" class="aspect-[245/342] w-full object-cover">
                        @endif
                    </div>
                    <p class="mt-3 line-clamp-1 text-center text-sm font-bold">{{ $card->name }}</p>
                    <p class="text-center text-[10px] uppercase tracking-widest text-white/60">{{ $card->rarity ?? 'Common' }}</p>
                </a>
            @endforeach
        </div>

        <div class="mt-14 flex flex-wrap justify-center gap-3">
            <form method="POST" action="{{ route('packs.open') }}">
                @csrf
                <button type="submit" class="group relative inline-flex items-center gap-2 overflow-hidden rounded-full px-7 py-3.5 text-base font-bold text-white shadow-xl transition hover:scale-[1.02]">
                    <span class="absolute inset-0 prism-bg"></span>
                    <span class="relative font-display text-sm font-black uppercase tracking-widest">Open another — @idr(49000)</span>
                </button>
            </form>
            <x-prism-button :href="route('cards.index')" variant="ghost" size="md">Back to shop</x-prism-button>
        </div>
    </div>
</section>

</x-app-layout>
