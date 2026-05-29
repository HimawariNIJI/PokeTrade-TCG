<x-app-layout
    title="Price Tracker"
    description="Browse and track every Pokemon TCG Prismatic Evolutions card with live market values. Filter by set, type, rarity, and regulation, and watch your chase cards.">

{{-- =====================================================
     PAGE HEADER
     ===================================================== --}}
<x-page-hero
    compact
    mon="eevee"
    eyebrow="Price tracker · Scarlet &amp; Violet"
    title='<span class="prism-text">Price</span> tracker.'
    subtitle="Track every Prismatic Evolutions card's market value. Filter by set, type, rarity, and regulation, sort by price, and add cards to your chase list to watch what they're worth.">
    <x-slot:actions>
        <span class="inline-flex items-center gap-2 rounded-2xl border border-ink-200 bg-white px-4 py-2.5 shadow-[var(--shadow-soft)]">
            <span class="font-display text-2xl font-bold prism-text">{{ number_format($cards->total()) }}</span>
            <span class="text-xs uppercase tracking-widest text-ink-500">cards tracked</span>
        </span>
    </x-slot:actions>
</x-page-hero>

{{-- =====================================================
     FILTER BAR (sticky)
     ===================================================== --}}
<form method="GET" action="{{ route('cards.index') }}" class="sticky top-[68px] z-20 border-y border-ink-200 bg-white/90 backdrop-blur" x-data="{ q: '{{ request('q') }}' }">
    <div class="mx-auto flex max-w-[1400px] flex-wrap items-center gap-3 px-4 py-3 md:px-8">
        {{-- Search --}}
        <label class="relative flex-1 min-w-[220px]">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="search" name="q" x-model="q"
                   value="{{ request('q') }}" placeholder="Search by name (Eevee, Umbreon ex…)"
                   class="w-full rounded-full border-ink-200 pl-10 pr-4 text-sm focus:border-prism-violet focus:ring-prism-violet" />
        </label>

        <select name="set" aria-label="Filter by set" class="rounded-full border-ink-200 text-sm focus:border-prism-violet focus:ring-prism-violet">
            <option value="">All sets</option>
            @foreach($allSets as $s)
                <option value="{{ $s->set_id }}" @selected(request('set') === $s->set_id)>{{ $s->set_name }}</option>
            @endforeach
        </select>

        <select name="supertype" aria-label="Filter by category" class="rounded-full border-ink-200 text-sm focus:border-prism-violet focus:ring-prism-violet">
            <option value="">All categories</option>
            @foreach(['Pokémon', 'Trainer', 'Energy'] as $st)
                <option value="{{ $st }}" @selected(request('supertype') === $st)>{{ $st }}</option>
            @endforeach
        </select>

        <select name="type" aria-label="Filter by type" class="rounded-full border-ink-200 text-sm focus:border-prism-violet focus:ring-prism-violet">
            <option value="">All types</option>
            @foreach($allTypes as $t)
                <option value="{{ $t }}" @selected(request('type') === $t)>{{ $t }}</option>
            @endforeach
        </select>

        <select name="rarity" aria-label="Filter by rarity" class="rounded-full border-ink-200 text-sm focus:border-prism-violet focus:ring-prism-violet">
            <option value="">All rarities</option>
            @foreach($allRarities as $r)
                <option value="{{ $r }}" @selected(request('rarity') === $r)>{{ $r }}</option>
            @endforeach
        </select>

        <select name="regulation" aria-label="Filter by regulation mark" class="rounded-full border-ink-200 text-sm focus:border-prism-violet focus:ring-prism-violet">
            <option value="">All regulations</option>
            @foreach($allRegMarks as $m)
                <option value="{{ $m }}" @selected(request('regulation') === $m)>Reg. {{ $m }}</option>
            @endforeach
        </select>

        <div class="ml-auto flex flex-wrap items-center gap-3">
            <select name="sort" aria-label="Sort cards" class="rounded-full border-ink-200 text-sm focus:border-prism-violet focus:ring-prism-violet">
                <option value="number"     @selected(request('sort', 'number') === 'number')>Sort: by number</option>
                <option value="name"       @selected(request('sort') === 'name')>Sort: by name</option>
                <option value="price_asc"  @selected(request('sort') === 'price_asc')>Price: low → high</option>
                <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high → low</option>
                <option value="rarity"     @selected(request('sort') === 'rarity')>Sort: by rarity</option>
            </select>

            <button type="submit" class="rounded-full bg-ink-900 px-5 py-2 text-sm font-bold text-white hover:bg-ink-700">
                Apply
            </button>
            <a href="{{ route('cards.index') }}" class="text-sm text-ink-500 hover:text-ink-900">Reset</a>
        </div>
    </div>
</form>

{{-- =====================================================
     ACTIVE FILTER CHIPS
     ===================================================== --}}
@if(request()->hasAny(['q', 'type', 'supertype', 'rarity', 'set', 'regulation']))
    <div class="mx-auto max-w-[1400px] px-4 pt-4 md:px-8">
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="text-ink-500">Active filters:</span>
            @foreach(['q' => 'Search', 'set' => 'Set', 'supertype' => 'Category', 'type' => 'Type', 'rarity' => 'Rarity', 'regulation' => 'Reg.'] as $key => $label)
                @if(request($key))
                    @php
                        $value = request($key);
                        if ($key === 'set') {
                            $value = $allSets->firstWhere('set_id', $value)?->set_name ?? $value;
                        }
                    @endphp
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-ink-900 px-3 py-1 font-semibold text-white">
                        {{ $label }}: {{ $value }}
                        <a href="{{ request()->fullUrlWithoutQuery($key) }}" class="text-white/60 hover:text-white">×</a>
                    </span>
                @endif
            @endforeach
        </div>
    </div>
@endif

{{-- =====================================================
     CARD GRID
     ===================================================== --}}
<section class="mx-auto max-w-[1400px] px-4 py-10 md:px-8 md:py-14">
    @if($cards->isNotEmpty())
        <div class="grid grid-cols-2 gap-x-5 gap-y-10 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
            @foreach($cards as $card)
                <x-card-tile :card="$card" />
            @endforeach
        </div>

        <div class="mt-12">
            {{ $cards->links() }}
        </div>
    @else
        <x-empty-state title="No cards match those filters" message="Try clearing some filters or searching for a different name.">
            <x-prism-button :href="route('cards.index')" size="md">Clear all filters</x-prism-button>
        </x-empty-state>
    @endif
</section>

</x-app-layout>
