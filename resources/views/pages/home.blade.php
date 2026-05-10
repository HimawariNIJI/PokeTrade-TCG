<x-app-layout>

{{-- =====================================================
     HERO
     ===================================================== --}}
<section class="relative isolate overflow-hidden">
    <div class="absolute inset-0 -z-10 halftone opacity-60"></div>
    <div class="absolute -top-24 right-0 -z-10 hidden h-[120%] w-[55%] -rotate-12 md:block">
        <div class="absolute inset-y-0 left-0 w-12 prism-bg opacity-25 blur-sm"></div>
        <div class="absolute inset-y-0 left-24 w-6 prism-bg opacity-15"></div>
        <div class="absolute inset-y-0 left-36 w-4 prism-bg opacity-25"></div>
        <div class="absolute inset-y-0 left-44 w-2 prism-bg opacity-40"></div>
    </div>
    <div class="pointer-events-none absolute -left-32 top-1/3 -z-10 h-96 w-96 rounded-full bg-prism-pink/20 blur-3xl"></div>
    <div class="pointer-events-none absolute right-1/3 top-0 -z-10 h-96 w-96 rounded-full bg-prism-mint/20 blur-3xl"></div>

    <div class="relative mx-auto grid max-w-[1400px] grid-cols-1 items-center gap-12 px-4 pb-24 pt-16 md:px-8 md:pt-20 lg:grid-cols-12 lg:gap-8 lg:pb-32">
        <div class="lg:col-span-6 xl:col-span-7">
            <div class="inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white/70 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-ink-700 backdrop-blur">
                <span class="inline-block h-2 w-2 rounded-full bg-prism-mint"></span>
                Scarlet &amp; Violet · sv8pt5 · 180 cards live
            </div>

            <h1 class="mt-5 font-display text-5xl font-black leading-[0.95] tracking-tight md:text-7xl xl:text-[5.75rem]">
                <span class="block text-ink-900">Trade the</span>
                <span class="prism-text block">Prismatic</span>
                <span class="block text-ink-900">Evolutions.</span>
            </h1>

            <p class="mt-6 max-w-xl text-base leading-relaxed text-ink-700 md:text-lg">
                Eevee and its rainbow of evolutions in <strong>special illustration rares</strong>, hyper rares, and shiny holos — buy, sell, auction, and trade them here. Your binder, your terms.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <x-prism-button :href="route('cards.index')" size="lg">
                    Browse the card shop
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/></svg>
                </x-prism-button>
                <x-prism-button :href="route('auctions.index')" variant="ghost" size="lg">Live auctions</x-prism-button>
                <x-prism-button :href="route('packs.index')" variant="outline" size="lg">Open a pack</x-prism-button>
            </div>

            <dl class="mt-10 grid max-w-lg grid-cols-3 gap-8 border-t border-ink-200 pt-8">
                <div>
                    <dt class="font-display text-3xl font-black prism-text">180</dt>
                    <dd class="mt-1 text-xs uppercase tracking-widest text-ink-500">cards in set</dd>
                </div>
                <div>
                    <dt class="font-display text-3xl font-black prism-text">24/7</dt>
                    <dd class="mt-1 text-xs uppercase tracking-widest text-ink-500">live auctions</dd>
                </div>
                <div>
                    <dt class="font-display text-3xl font-black prism-text">∞</dt>
                    <dd class="mt-1 text-xs uppercase tracking-widest text-ink-500">trade combos</dd>
                </div>
            </dl>
        </div>

        {{-- Hero card stack — flippable, not floating. Click to flip. --}}
        <div class="relative lg:col-span-6 xl:col-span-5">
            <div class="relative mx-auto h-[460px] w-full max-w-[520px] md:h-[560px]">
                @php $heroCards = $featuredCards->take(3); @endphp

                @foreach($heroCards as $i => $card)
                    @php
                        $rot   = [-9, 4, 12][$i] ?? 0;
                        $tx    = [-90, 0, 90][$i] ?? 0;
                        $ty    = [40, 0, 30][$i] ?? 0;
                        $z     = [10, 30, 20][$i] ?? 10;
                        $delay = $i * 0.6;
                    @endphp
                    {{-- Outer wrapper does the static layout transform (translate+rotate)
                         AND the idle float bob (translateY oscillation via animation). --}}
                    <div class="absolute left-1/2 top-1/2 w-[230px] md:w-[280px]"
                         style="transform: translate(calc(-50% + {{ $tx }}px), calc(-50% + {{ $ty }}px)) rotate({{ $rot }}deg); z-index: {{ $z }};">
                        <a href="{{ route('cards.show', $card) }}"
                           class="group relative block animate-float"
                           style="animation-delay: {{ $delay }}s;">
                            <div class="prism-halo-glow always-on opacity-40"></div>
                            <x-tilted-card
                                :src="$card->image_large ?? $card->image_small"
                                :alt="$card->name"
                                :rotate="14"
                                :scaleOnHover="1.05"
                                innerClass="shadow-2xl ring-1 ring-white/50"
                            />
                        </a>
                    </div>
                @endforeach

                <div class="pointer-events-none absolute right-0 top-0 h-12 w-12 rounded-tr-2xl border-r-2 border-t-2 border-prism-violet/40"></div>
                <div class="pointer-events-none absolute bottom-0 left-0 h-12 w-12 rounded-bl-2xl border-b-2 border-l-2 border-prism-pink/40"></div>
            </div>
        </div>
    </div>

    {{-- Bottom CTA strip --}}
    <div class="relative">
        <div class="absolute inset-x-0 bottom-0 -z-10 h-20 prism-bg opacity-90"></div>
        <div class="mx-auto grid max-w-[1400px] grid-cols-1 divide-y divide-white/30 text-white md:grid-cols-3 md:divide-x md:divide-y-0">
            <a href="{{ route('cards.index') }}" class="group flex items-center justify-center gap-3 px-6 py-5 font-display text-sm font-bold uppercase tracking-widest transition hover:bg-white/10">
                See full card list
                <svg class="h-4 w-4 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/></svg>
            </a>
            <a href="{{ route('shop.index') }}" class="group flex items-center justify-center gap-3 px-6 py-5 font-display text-sm font-bold uppercase tracking-widest transition hover:bg-white/10">
                Booster Boxes &amp; Merch
                <svg class="h-4 w-4 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/></svg>
            </a>
            <a href="{{ route('about') }}" class="group flex items-center justify-center gap-3 px-6 py-5 font-display text-sm font-bold uppercase tracking-widest transition hover:bg-white/10">
                About Prismatic Evolutions
                <svg class="h-4 w-4 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- =====================================================
     HOW IT WORKS — editorial zigzag, real cards, no template grid
     ===================================================== --}}
@php
    $rowCards = $featuredCards->shuffle()->values();
    $row1Card = $rowCards->get(0);
    $row2CardA = $rowCards->get(1);
    $row2CardB = $rowCards->get(2);
    $row3CardOffer = $rowCards->get(3) ?? $rowCards->get(0);
    $row3CardWant  = $rowCards->get(4) ?? $rowCards->get(1);
@endphp

<section class="mx-auto max-w-[1400px] px-4 py-24 md:px-8">
    <div class="max-w-2xl">
        <span class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.3em] text-ink-500">
            <span class="h-px w-8 bg-ink-300"></span> How PokeTrade works
        </span>
        <h2 class="mt-4 font-display text-4xl font-black tracking-tight md:text-5xl">
            Three workflows.<br/><span class="prism-text">One marketplace.</span>
        </h2>
    </div>

    {{-- ROW 1 — BUY & SELL: 7/5 split, big card on right --}}
    <article class="mt-16 grid items-center gap-10 lg:grid-cols-12">
        <div class="lg:col-span-7">
            <p class="font-mono text-[11px] font-bold uppercase tracking-widest text-ink-500">01 — Buy &amp; sell</p>
            <h3 class="mt-2 font-display text-3xl font-black leading-tight md:text-4xl">
                List your dupes.<br/>Snag your <em class="prism-text not-italic">grail</em>.
            </h3>
            <p class="mt-4 max-w-md text-ink-700">
                Every Prismatic Evolutions card is browsable with live market value vs. our house price. Sort by rarity, filter by type, click <em>add to cart</em>. Done in 30 seconds — no sketchy DMs, no marketplace fees from <span class="line-through">that other site</span>.
            </p>
            <div class="mt-6 flex flex-wrap gap-x-8 gap-y-3 text-sm">
                <div>
                    <p class="font-mono text-2xl font-black text-ink-900">180</p>
                    <p class="text-xs text-ink-500">cards live now</p>
                </div>
                <div>
                    <p class="font-mono text-2xl font-black text-ink-900">@idr(5000)–@idr(3500000)</p>
                    <p class="text-xs text-ink-500">price range</p>
                </div>
                <div>
                    <p class="font-mono text-2xl font-black text-ink-900">10%</p>
                    <p class="text-xs text-ink-500">house markup</p>
                </div>
            </div>
            <a href="{{ route('cards.index') }}" class="mt-8 inline-flex items-center gap-2 font-display text-sm font-bold text-ink-900 hover:text-prism-violet">
                Open the card shop
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/></svg>
            </a>
        </div>

        <div class="relative lg:col-span-5">
            <div class="absolute -inset-6 -z-10 rounded-[3rem] prism-bg opacity-20 blur-2xl"></div>
            <div class="mx-auto w-full max-w-[280px]" style="transform: rotate(-2deg);">
                <x-tilted-card
                    :src="$row1Card?->image_large"
                    :alt="$row1Card?->name ?? 'Card'"
                    :rotate="14"
                    :scaleOnHover="1.05"
                />
            </div>
            @if($row1Card)
                <div class="absolute -right-4 -top-4 rounded-2xl bg-white px-3 py-2 shadow-xl ring-1 ring-ink-200">
                    <p class="text-[10px] uppercase tracking-widest text-ink-500">Listed at</p>
                    <p class="font-display text-lg font-black prism-text">@idr($row1Card->display_price)</p>
                </div>
            @endif
        </div>
    </article>

    {{-- ROW 2 — AUCTIONS: dark slab, ticker on left, copy right --}}
    <article class="mt-24 overflow-hidden rounded-3xl bg-ink-900 text-white">
        <div class="grid items-center gap-0 lg:grid-cols-12">
            <div class="relative p-8 md:p-12 lg:col-span-6">
                <p class="font-mono text-[11px] font-bold uppercase tracking-widest text-white/60">02 — Live auctions</p>
                <h3 class="mt-2 font-display text-3xl font-black leading-tight md:text-4xl">
                    Bid in real time.<br/>Snipe at the <span class="prism-text">final second</span>.
                </h3>

                {{-- Live "ticker" mockup — uses real card data --}}
                <div class="mt-6 space-y-2 rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-white/60">
                        <span class="inline-block h-2 w-2 rounded-full bg-rose-500"></span> Live · 3 bids in last 12s
                    </div>
                    @foreach([
                        ['name' => $row2CardA?->name ?? 'Eevee ex', 'amount' => 1850000, 'leader' => 'kanto.trainer'],
                        ['name' => $row2CardB?->name ?? 'Umbreon ex', 'amount' => 4200000, 'leader' => 'gymleader.giovanni'],
                    ] as $tick)
                        <div class="flex items-center justify-between border-t border-white/10 pt-2 text-sm">
                            <span class="line-clamp-1 font-semibold">{{ $tick['name'] }}</span>
                            <div class="flex items-center gap-3 text-right">
                                <span class="font-mono text-xs text-white/60">@ {{ $tick['leader'] }}</span>
                                <span class="font-mono font-bold prism-text">@idr($tick['amount'])</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <a href="{{ route('auctions.index') }}" class="mt-6 inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 font-display text-sm font-bold text-ink-900 hover:bg-prism-gold">
                    Watch live bids
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/></svg>
                </a>
            </div>

            <div class="relative bg-gradient-to-br from-prism-violet/40 via-prism-pink/30 to-prism-sky/40 p-8 md:p-12 lg:col-span-6">
                <div class="mx-auto w-full max-w-[260px]" style="transform: rotate(3deg);">
                    <x-tilted-card
                        :src="$row2CardB?->image_large"
                        :alt="$row2CardB?->name ?? 'Card'"
                        :rotate="14"
                        :scaleOnHover="1.05"
                    />
                </div>
                <div class="absolute left-6 top-6 inline-flex items-center gap-1.5 rounded-full bg-rose-600 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-white shadow-lg">
                    <span class="h-1.5 w-1.5 rounded-full bg-white"></span> Ends in 02:14
                </div>
            </div>
        </div>
    </article>

    {{-- ROW 3 — TRADE: two cards mid, copy on the sides --}}
    <article class="mt-24 grid items-center gap-10 lg:grid-cols-12">
        <div class="order-2 lg:order-1 lg:col-span-3">
            <p class="font-mono text-[11px] font-bold uppercase tracking-widest text-ink-500">03 — Trade</p>
            <h3 class="mt-2 font-display text-3xl font-black leading-tight md:text-4xl">
                Cash-free<br/><span class="prism-text">swaps</span>.
            </h3>
            <p class="mt-4 text-sm text-ink-700">
                Offer dupes. Request grails. Both sides confirm — done. No fees, no delivery hops, just two binders meeting in the middle.
            </p>
        </div>

        <div class="relative order-1 grid grid-cols-2 items-center gap-2 lg:order-2 lg:col-span-6">
            {{-- "you offer" card --}}
            <div class="relative">
                <p class="absolute -top-7 left-2 z-10 text-[10px] font-bold uppercase tracking-widest text-prism-violet">You offer</p>
                <div style="transform: rotate(-3deg);">
                    <x-tilted-card
                        :src="$row3CardOffer?->image_large"
                        alt="Offer card"
                        :rotate="14"
                        :scaleOnHover="1.05"
                    />
                </div>
            </div>

            {{-- handshake / arrows --}}
            <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-10">
                <div class="flex items-center justify-center rounded-full bg-white p-3 shadow-xl ring-2 ring-prism-violet/40">
                    <svg class="h-6 w-6 text-prism-violet" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                </div>
            </div>

            {{-- "they offer" card --}}
            <div class="relative">
                <p class="absolute -top-7 right-2 z-10 text-[10px] font-bold uppercase tracking-widest text-prism-mint">You request</p>
                <div style="transform: rotate(3deg);">
                    <x-tilted-card
                        :src="$row3CardWant?->image_large"
                        alt="Request card"
                        :rotate="14"
                        :scaleOnHover="1.05"
                    />
            </div>
        </div>

        <div class="order-3 lg:col-span-3 lg:text-right">
            <p class="text-xs text-ink-500">Both trainers confirm</p>
            <p class="mt-1 font-display text-2xl font-black text-ink-900">→ ✓</p>
            <a href="{{ route('trades.index') }}" class="mt-6 inline-flex items-center gap-2 font-display text-sm font-bold text-ink-900 hover:text-prism-violet lg:flex-row-reverse">
                Open the trade floor
                <svg class="h-4 w-4 lg:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/></svg>
            </a>
        </div>
    </article>
</section>

{{-- =====================================================
     FEATURED CARDS — the hot drops grid
     ===================================================== --}}
<section class="relative">
    <div class="absolute inset-x-0 top-0 -z-10 h-1/2 bg-gradient-to-b from-ink-50 to-transparent"></div>

    <div class="mx-auto max-w-[1400px] px-4 py-20 md:px-8">
        <div class="flex items-end justify-between gap-6">
            <x-section-heading
                eyebrow="Hot drops"
                title='Featured <span class="prism-text">illustration rares</span>'
                subtitle="The cards trainers are chasing right now — limited stock, holographic, fully Prismatic." />

            <a href="{{ route('cards.index') }}?sort=rarity" class="hidden items-center gap-2 text-sm font-bold text-ink-900 hover:text-prism-violet sm:inline-flex">
                See all cards
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/></svg>
            </a>
        </div>

        @if($featuredCards->isNotEmpty())
            <div class="mt-10 grid grid-cols-2 gap-x-6 gap-y-10 md:grid-cols-3 lg:grid-cols-6">
                @foreach($featuredCards as $card)
                    <x-card-tile :card="$card" />
                @endforeach
            </div>
        @else
            <x-empty-state title="No featured cards yet" message="Run the seeder to populate cards." />
        @endif
    </div>
</section>

{{-- =====================================================
     MERCH STRIP
     ===================================================== --}}
@if($featuredItems->isNotEmpty())
<section class="mx-auto max-w-[1400px] px-4 pb-20 md:px-8">
    <div class="overflow-hidden rounded-3xl bg-ink-900 text-white">
        <div class="grid gap-0 lg:grid-cols-12">
            <div class="flex flex-col justify-between p-8 md:p-12 lg:col-span-4">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest">
                        <span class="h-2 w-2 rounded-full bg-prism-gold"></span> Custom shop
                    </span>
                    <h2 class="mt-4 font-display text-4xl font-black leading-tight md:text-5xl">
                        Boxes,<br/>bundles,<br/><span class="prism-text">plushies</span>.
                    </h2>
                    <p class="mt-4 text-sm text-white/70">Sealed product, sleeves, playmats, and the merch you actually want — admin-curated, image-uploaded, ready to ship.</p>
                </div>
                <x-prism-button :href="route('shop.index')" size="md" class="mt-8 self-start">Shop the merch</x-prism-button>
            </div>
            <div class="grid grid-cols-2 gap-3 bg-white p-6 md:gap-4 md:p-8 lg:col-span-8 lg:grid-cols-4">
                @foreach($featuredItems as $item)
                    <x-shop-tile :item="$item" />
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

{{-- =====================================================
     CTA — sign up
     ===================================================== --}}
@guest
<section class="mx-auto max-w-[1400px] px-4 pb-24 md:px-8">
    <div class="relative overflow-hidden rounded-3xl border border-ink-200 bg-white p-10 md:p-16">
        <div class="absolute -right-32 -top-32 h-80 w-80 rounded-full prism-bg opacity-20 blur-3xl"></div>
        <div class="absolute -bottom-32 -left-16 h-80 w-80 rounded-full bg-prism-mint/30 blur-3xl"></div>

        <div class="relative grid items-center gap-10 md:grid-cols-2">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-ink-200 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-ink-700">
                    Free to join
                </span>
                <h2 class="mt-4 font-display text-3xl font-black tracking-tight md:text-5xl">
                    Build your binder.<br/>
                    <span class="prism-text">Trade your way in.</span>
                </h2>
                <p class="mt-4 max-w-md text-ink-700">
                    Sign up to save a wishlist, place bids, propose trades, and open packs. Free, clean, prismatic.
                </p>
            </div>
            <div class="flex flex-col gap-3 md:items-end">
                <x-prism-button :href="route('register')" size="lg">Create your account</x-prism-button>
                <p class="text-xs text-ink-500">
                    Already a trainer? <a href="{{ route('login') }}" class="font-bold text-ink-900 underline-offset-4 hover:underline">Log in →</a>
                </p>
            </div>
        </div>
    </div>
</section>
@endguest

</x-app-layout>
