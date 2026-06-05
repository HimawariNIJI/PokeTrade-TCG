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

    {{-- Flash from a pin/unpin action. --}}
    @if(session('status'))
        <div x-data="{ show: true }" x-show="show" x-transition
             x-init="setTimeout(() => show = false, 4000)"
             class="mb-8 rounded-2xl border border-prism-mint/40 bg-prism-mint/10 px-4 py-3 text-sm font-semibold text-ink-900">
            {{ session('status') }}
        </div>
    @endif

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
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <select
                    onchange="changeRarity(this.value)"
                    class="rounded-full border-ink-200 text-sm focus:border-prism-violet focus:ring-prism-violet">
                    <option value="">All rarities</option>
                    @foreach($allRarities as $r)
                        <option value="{{ $r }}" @selected(request('rarity') === $r)>
                            {{ $r }}
                        </option>
                    @endforeach
                </select>
            </div>
            <label class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-ink-500">
                Cards per page
                <select
                    onchange="changePerPage(this.value)"
                    class="rounded-full border-ink-200 px-3 py-1.5 pr-8 text-xs font-bold text-ink-900">
                    @foreach($allowedPerPage as $opt)
                        <option value="{{ $opt }}" @selected($perPage == $opt)>
                            {{ $opt }}
                        </option>
                    @endforeach
                </select>
            </label>
        </div>
        <script>
            function changeRarity(value) {
                const url = new URL(window.location.href);
                if (value) {
                    url.searchParams.set('rarity', value);
                } else {
                    url.searchParams.delete('rarity');
                }
                url.searchParams.delete('page');
                window.location.href = url.toString();
            }
            function changePerPage(value) {
                const u = new URL(window.location.href);
                u.searchParams.set('per_page', value);
                u.searchParams.delete('page');
                window.location.href = u.toString();
            }
        </script>

        {{-- Pinned summary + a hint to the settings picker when at cap. --}}
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-prism-gold/40 bg-prism-gold/10 px-4 py-3">
            <div class="flex items-center gap-2 text-sm text-ink-700">
                <svg class="h-4 w-4 text-prism-violet" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2z"/>
                </svg>
                <span><strong>{{ $pinnedIds->count() }}</strong> of {{ $maxPinned }} cards pinned to your profile.</span>
            </div>
            <a href="{{ route('settings.edit') }}" class="text-xs font-bold uppercase tracking-widest text-prism-violet hover:underline">
                Manage in settings
            </a>
        </div>

        <div class="grid grid-cols-2 gap-x-5 gap-y-10 md:grid-cols-4 lg:grid-cols-6">
            @foreach($cards as $card)
                @php $isPinned = $pinnedIds->contains($card->id); @endphp
                <div class="relative">
                    {{-- Pin toggle — sits over the top-right of the tile.
                         Submits a tiny POST form so it works without JS. --}}
                    <form method="POST" action="{{ route('settings.pin', $card->id) }}"
                          class="absolute right-1 top-1 z-20">
                        @csrf
                        <button type="submit"
                                title="{{ $isPinned ? 'Unpin from profile' : 'Pin to profile' }}"
                                aria-label="{{ $isPinned ? 'Unpin from profile' : 'Pin to profile' }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-full shadow-md transition
                                       {{ $isPinned
                                            ? 'bg-prism-violet text-white hover:bg-prism-violet/90'
                                            : 'bg-white/95 text-ink-500 hover:bg-white hover:text-prism-violet' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2z"/>
                            </svg>
                        </button>
                    </form>
                    <x-collection-card :card="$card" />
                </div>
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
