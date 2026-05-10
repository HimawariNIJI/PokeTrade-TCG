<x-app-layout>

{{-- =====================================================
     PAGE HEADER
     ===================================================== --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10 halftone opacity-50"></div>
    <div class="absolute -top-32 right-0 -z-10 h-96 w-96 rounded-full bg-prism-pink/15 blur-3xl"></div>
    <div class="absolute -top-32 left-0 -z-10 h-96 w-96 rounded-full bg-prism-mint/15 blur-3xl"></div>

    <div class="mx-auto max-w-[1400px] px-4 pb-10 pt-16 md:px-8 md:pb-12 md:pt-20">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white/70 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-ink-700 backdrop-blur">
                    Scarlet &amp; Violet · Prismatic Evolutions
                </span>
                <h1 class="mt-4 font-display text-5xl font-black tracking-tight md:text-6xl">
                    The <span class="prism-text">Card Shop</span>.
                </h1>
                <p class="mt-3 max-w-2xl text-ink-700">
                    All 180 cards from the set, with live market prices and our house pricing. Filter, search, sort, and add to cart.
                </p>
            </div>

            <div class="rounded-2xl border border-ink-200 bg-white px-4 py-3 text-right">
                <p class="text-[11px] uppercase tracking-widest text-ink-500">Showing</p>
                <p class="font-display text-2xl font-black text-ink-900">{{ $cards->total() }}<span class="text-ink-500"> / 180</span></p>
            </div>
        </div>
    </div>
</section>

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

        <select name="supertype" class="rounded-full border-ink-200 text-sm focus:border-prism-violet focus:ring-prism-violet">
            <option value="">All categories</option>
            @foreach(['Pokémon', 'Trainer', 'Energy'] as $st)
                <option value="{{ $st }}" @selected(request('supertype') === $st)>{{ $st }}</option>
            @endforeach
        </select>

        <select name="type" class="rounded-full border-ink-200 text-sm focus:border-prism-violet focus:ring-prism-violet">
            <option value="">All types</option>
            @foreach($allTypes as $t)
                <option value="{{ $t }}" @selected(request('type') === $t)>{{ $t }}</option>
            @endforeach
        </select>

        <select name="rarity" class="rounded-full border-ink-200 text-sm focus:border-prism-violet focus:ring-prism-violet">
            <option value="">All rarities</option>
            @foreach($allRarities as $r)
                <option value="{{ $r }}" @selected(request('rarity') === $r)>{{ $r }}</option>
            @endforeach
        </select>

        <select name="sort" class="rounded-full border-ink-200 text-sm focus:border-prism-violet focus:ring-prism-violet">
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
</form>

{{-- =====================================================
     ACTIVE FILTER CHIPS
     ===================================================== --}}
@if(request()->hasAny(['q', 'type', 'supertype', 'rarity']))
    <div class="mx-auto max-w-[1400px] px-4 pt-4 md:px-8">
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="text-ink-500">Active filters:</span>
            @foreach(['q' => 'Search', 'supertype' => 'Category', 'type' => 'Type', 'rarity' => 'Rarity'] as $key => $label)
                @if(request($key))
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-ink-900 px-3 py-1 font-semibold text-white">
                        {{ $label }}: {{ request($key) }}
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
