<x-app-layout>

{{-- =====================================================
     DIGITAL COLLECTION — every card the trainer has pulled
     from the gacha. Stats strip up top, grid below.
     ===================================================== --}}
<section class="mx-auto max-w-[1400px] px-4 py-16 md:px-8">
    <div class="mb-10 flex flex-wrap items-end justify-between gap-6">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-ink-200 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-ink-700">
                Pulled from the gacha
            </span>
            <h1 class="mt-3 font-display text-4xl font-black tracking-tight md:text-5xl">
                My digital <span class="prism-text">collection</span>.
            </h1>
            <p class="mt-2 text-sm text-ink-700">
                {{ $uniqueCards }} unique card{{ $uniqueCards === 1 ? '' : 's' }} ·
                {{ $totalCards }} total pull{{ $totalCards === 1 ? '' : 's' }}.
            </p>
        </div>

        @if($uniqueCards > 0)
            <div class="flex flex-wrap gap-3">
                <x-prism-button :href="route('collection.history')" variant="ghost" size="md">Pull history</x-prism-button>
                <x-prism-button :href="route('gacha.index')" size="md">Pull another pack</x-prism-button>
            </div>
        @endif
    </div>

    @if($uniqueCards > 0)
        {{-- ============ STATS STRIP ============ --}}
        <div class="mb-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-ink-200 bg-white px-5 py-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500">Total cards</p>
                <p class="mt-1 font-display text-3xl font-black text-ink-900">{{ $totalCards }}</p>
                <p class="mt-1 text-xs text-ink-500">Every pull, duplicates included.</p>
            </div>
            <div class="rounded-2xl border border-ink-200 bg-white px-5 py-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500">Unique cards</p>
                <p class="mt-1 font-display text-3xl font-black prism-text">{{ $uniqueCards }}</p>
                <p class="mt-1 text-xs text-ink-500">Distinct cards in your binder.</p>
            </div>
            <div class="rounded-2xl border border-ink-200 bg-white px-5 py-4 sm:col-span-2">
                <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500">By rarity</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach($rarityBreakdown as $rarity => $count)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-ink-200 bg-ink-50 px-3 py-1 text-xs font-semibold text-ink-700">
                            {{ $rarity }}
                            <span class="font-mono text-[11px] text-ink-500">×{{ $count }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ============ CARD DISPLAY ============
             A clean display of just the cards the trainer has picked
             up from the gacha — no prices, no market value. --}}
        <div class="mb-5 flex flex-wrap items-center justify-end gap-3">
            <label class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-ink-500">
                Cards per page
                <select
                    onchange="const u=new URL(window.location); u.searchParams.set('per_page', this.value); u.searchParams.delete('page'); window.location = u.toString();"
                    class="rounded-full border-ink-200 px-3 py-1.5 text-xs font-bold text-ink-900">
                    @foreach($allowedPerPage as $opt)
                        <option value="{{ $opt }}" @selected($perPage === $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="grid grid-cols-2 gap-x-5 gap-y-10 md:grid-cols-4 lg:grid-cols-6">
            @foreach($cards as $card)
                <x-collection-card :card="$card" />
            @endforeach
        </div>

        <div class="mt-10">{{ $cards->links() }}</div>
    @else
        <x-empty-state
            icon="✦"
            title="Your collection is empty"
            message="Pull a digital pack to drop your first 5 cards into your binder.">
            <x-prism-button :href="route('gacha.index')" size="md">Pull your first pack</x-prism-button>
        </x-empty-state>
    @endif
</section>

</x-app-layout>
