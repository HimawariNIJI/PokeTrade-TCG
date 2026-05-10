<x-app-layout>

<section class="mx-auto max-w-[1400px] px-4 py-16 md:px-8">
    <div class="mb-10 flex flex-wrap items-end justify-between gap-6">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-ink-200 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-ink-700">
                Saved for later
            </span>
            <h1 class="mt-3 font-display text-4xl font-black tracking-tight md:text-5xl">
                My <span class="prism-text">wishlist</span>.
            </h1>
            <p class="mt-2 text-sm text-ink-700">{{ $cards->total() }} card{{ $cards->total() === 1 ? '' : 's' }} saved.</p>
        </div>
    </div>

    @if($cards->isEmpty())
        <x-empty-state
            icon="❤"
            title="No cards saved yet"
            message="Tap the heart on any card detail page to add it to your wishlist.">
            <x-prism-button :href="route('cards.index')" size="md">Browse cards</x-prism-button>
        </x-empty-state>
    @else
        <div class="grid grid-cols-2 gap-x-5 gap-y-10 md:grid-cols-4 lg:grid-cols-6">
            @foreach($cards as $card)
                <x-card-tile :card="$card" />
            @endforeach
        </div>
        <div class="mt-10">{{ $cards->links() }}</div>
    @endif
</section>

</x-app-layout>
