<x-app-layout
    title="Merch Shop"
    description="Official Pokemon TCG merch: sealed booster boxes, bundles, sleeves, playmats, and plushies. Curated drops, ready to ship.">


<section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10 halftone opacity-50"></div>
    <div class="mx-auto max-w-[1400px] px-4 pb-4 pt-10 md:px-8 md:pt-14">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white/70 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-ink-700 backdrop-blur"
                      data-reveal="pop">
                    Custom shop
                </span>
                <h1 class="mt-4 font-display text-5xl font-black tracking-tight md:text-6xl"
                    data-reveal="letters">
                    Boxes, plushies, <span class="prism-text">gear</span>.
                </h1>
                <p class="mt-3 max-w-2xl text-ink-700" data-reveal="fade-up" data-reveal-delay="250">Sealed product, sleeves, playmats and merch — admin-curated, image-uploaded, ready to ship.</p>
            </div>
        </div>
    </div>
</section>

<!-- Rewards Info Section -->
<section class="mx-auto max-w-[1400px] px-4 py-3 md:px-8 md:py-5">
    <div class="grid gap-3 md:grid-cols-2">
        <!-- Earn Points Card -->
        <div class="rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-emerald-100 p-6 md:p-8">
            <div class="flex items-center gap-4">
                <div class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-emerald-200/50">
                    <svg class="h-7 w-7 text-emerald-700" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.505 7.964a1 1 0 001.41-.007L12.9 5.573a1 1 0 10-1.414-1.414L9.5 5.146 7.614 3.259A1 1 0 006.2 4.673l2.305 2.291z"/>
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-display text-lg font-black text-ink-900">Earn Points</h3>
                    <p class="mt-2 text-sm text-ink-700">
                        Every purchase worth <span class="font-semibold">Rp 10,000</span> earns you <span class="font-semibold text-emerald-700">1 point</span>.
                    </p>
                    <p class="mt-2 text-xs text-ink-600">
                        💡 Buy Rp 100,000 worth of items → Get 10 points automatically after payment!
                    </p>
                </div>
            </div>
        </div>

        <!-- Redeem Points Card -->
        <div class="rounded-3xl border border-prism-violet/30 bg-gradient-to-br from-prism-violet/5 to-prism-pink/5 p-6 md:p-8">
            <div class="flex items-center gap-4">
                <div class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-prism-violet/10">
                    <svg class="h-7 w-7 text-prism-violet" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v4h8v-4zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-display text-lg font-black text-ink-900">Redeem for Gacha</h3>
                    <p class="mt-2 text-sm text-ink-700">
                        Collect <span class="font-semibold text-prism-violet">10 points</span> to <a href="{{ route('gacha.index') }}" class="text-prism-violet hover:text-prism-pink underline">pull a pack</a> in the <span class="font-semibold">Digital Gacha</span> system.
                    </p>
                    <p class="mt-2 text-xs text-ink-600">
                        🎁 Get exclusive adigital collectible cards with your earned points!
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<form method="GET" class="border-y border-ink-200 bg-white/90 backdrop-blur sticky top-[68px] z-20">
    <div class="mx-auto flex max-w-[1400px] flex-wrap items-center gap-3 px-4 py-3 md:px-8">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search merch…"
               class="flex-1 min-w-[200px] rounded-full border-ink-200 text-sm focus:border-prism-violet focus:ring-prism-violet" />
        <select name="category" aria-label="Filter by category" class="rounded-full border-ink-200 text-sm">
            <option value="">All categories</option>
            @foreach($categories as $c)
                <option value="{{ $c }}" @selected(request('category') === $c)>{{ ucfirst($c) }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-full bg-ink-900 px-5 py-2 text-sm font-bold text-white">Apply</button>
        <a href="{{ route('shop.index') }}" class="text-sm text-ink-500">Reset</a>
    </div>
</form>

<section class="mx-auto max-w-[1400px] px-4 py-10 md:px-8 md:py-14">
    @if($items->isNotEmpty())
        <div class="grid grid-cols-2 gap-5 md:grid-cols-3 lg:grid-cols-4">
            @foreach($items as $i => $item)
                @php $staggerIdx = $i % 4; @endphp
                {{-- zoom-in springs each merch tile straight at the viewer; reads
                     like products popping off the shelf. --}}
                <div data-reveal="zoom-in"
                     style="--reveal-i: {{ $staggerIdx }};">
                    <x-shop-tile :item="$item" />
                </div>
            @endforeach
        </div>
        <div class="mt-10" data-reveal="fade-up">{{ $items->links() }}</div>
    @else
        <x-empty-state title="No items yet" message="Admins can add merchandise from the admin panel." />
    @endif
</section>

</x-app-layout>
