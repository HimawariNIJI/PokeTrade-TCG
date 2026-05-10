<x-app-layout>

{{-- =====================================================
     HERO — riffs on tcg.pokemon.com's Prismatic Evolutions
     splash. Halftone-on-white backdrop, rainbow ribbon
     stripes diagonally across the right, three featured
     cards floating above with holographic sheen.
     ===================================================== --}}
<section class="relative isolate overflow-hidden">
    {{-- Halftone dots backdrop --}}
    <div class="absolute inset-0 -z-10 halftone opacity-60"></div>

    {{-- Diagonal prismatic ribbons (right side) --}}
    <div class="absolute -top-24 right-0 -z-10 hidden h-[120%] w-[55%] -rotate-12 md:block">
        <div class="absolute inset-y-0 left-0 w-12 prism-bg opacity-25 blur-sm"></div>
        <div class="absolute inset-y-0 left-24 w-6 prism-bg opacity-15"></div>
        <div class="absolute inset-y-0 left-36 w-4 prism-bg opacity-25"></div>
        <div class="absolute inset-y-0 left-44 w-2 prism-bg opacity-40"></div>
    </div>

    {{-- Soft halo behind the title --}}
    <div class="pointer-events-none absolute -left-32 top-1/3 -z-10 h-96 w-96 rounded-full bg-prism-pink/20 blur-3xl"></div>
    <div class="pointer-events-none absolute right-1/3 top-0 -z-10 h-96 w-96 rounded-full bg-prism-mint/20 blur-3xl"></div>

    <div class="relative mx-auto grid max-w-[1400px] grid-cols-1 items-center gap-12 px-4 pb-24 pt-16 md:px-8 md:pt-20 lg:grid-cols-12 lg:gap-8 lg:pb-32">
        <div class="lg:col-span-6 xl:col-span-7">
            <div class="inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white/70 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-ink-700 backdrop-blur">
                <span class="inline-block h-2 w-2 animate-pulse rounded-full bg-prism-mint"></span>
                Scarlet &amp; Violet · sv8pt5 · 180 cards live
            </div>

            <h1 class="mt-5 font-display text-5xl font-black leading-[0.95] tracking-tight md:text-7xl xl:text-[5.75rem]">
                <span class="block text-ink-900">Trade the</span>
                <span class="prism-text block">Prismatic</span>
                <span class="block text-ink-900">Evolutions.</span>
            </h1>

            <p class="mt-6 max-w-xl text-base leading-relaxed text-ink-700 md:text-lg">
                Eevee and its rainbow of evolutions in <strong>special illustration rares</strong>, hyper rares, and shiny holos — buy, sell, auction, and trade them right here. Your binder, your terms, your prismatic playground.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <x-prism-button :href="route('cards.index')" size="lg">
                    Browse the card shop
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/></svg>
                </x-prism-button>
                <x-prism-button :href="route('auctions.index')" variant="ghost" size="lg">
                    Live auctions
                </x-prism-button>
                <x-prism-button :href="route('gacha.index')" variant="outline" size="lg">
                    Try a gacha pull
                </x-prism-button>
            </div>

            {{-- Stats strip --}}
            <dl class="mt-10 grid max-w-lg grid-cols-3 gap-8 border-t border-ink-200 pt-8">
                @foreach([
                    ['stat' => '180', 'label' => 'cards in set'],
                    ['stat' => '24/7', 'label' => 'live auctions'],
                    ['stat' => '∞',   'label' => 'trade combos'],
                ] as $s)
                    <div>
                        <dt class="font-display text-3xl font-black prism-text">{{ $s['stat'] }}</dt>
                        <dd class="mt-1 text-xs uppercase tracking-widest text-ink-500">{{ $s['label'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        {{-- Hero card stack — three featured cards fanned out, holographic --}}
        <div class="relative lg:col-span-6 xl:col-span-5">
            <div class="relative mx-auto h-[460px] w-full max-w-[520px] md:h-[560px]">
                @php $heroCards = $featuredCards->take(3); @endphp

                @foreach($heroCards as $i => $card)
                    @php
                        $rot   = [-9, 4, 12][$i] ?? 0;
                        $tx    = [-90, 0, 90][$i] ?? 0;
                        $ty    = [40, 0, 30][$i] ?? 0;
                        $z     = [10, 30, 20][$i] ?? 10;
                        $delay = $i * 0.4;
                    @endphp
                    <a href="{{ route('cards.show', $card) }}"
                       class="group absolute left-1/2 top-1/2 block w-[230px] md:w-[280px] holo-sheen"
                       style="transform: translate(calc(-50% + {{ $tx }}px), calc(-50% + {{ $ty }}px)) rotate({{ $rot }}deg); z-index: {{ $z }}; animation: float 7s ease-in-out infinite; animation-delay: {{ $delay }}s;">
                        <div class="absolute -inset-3 rounded-3xl prism-bg opacity-40 blur-xl transition group-hover:opacity-70"></div>
                        <div class="card-tilt relative overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-white/50">
                            <img src="{{ $card->image_large ?? $card->image_small }}"
                                 alt="{{ $card->name }}"
                                 class="aspect-[245/342] w-full object-cover" />
                        </div>
                    </a>
                @endforeach

                {{-- Corner decorations --}}
                <div class="pointer-events-none absolute right-0 top-0 h-12 w-12 rounded-tr-2xl border-r-2 border-t-2 border-prism-violet/40"></div>
                <div class="pointer-events-none absolute bottom-0 left-0 h-12 w-12 rounded-bl-2xl border-b-2 border-l-2 border-prism-pink/40"></div>
            </div>
        </div>
    </div>

    {{-- Bottom CTA strip — echoes tcg.pokemon.com's button row --}}
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
     FEATURE HIGHLIGHTS — three pillars: Buy, Auction, Trade
     ===================================================== --}}
<section class="mx-auto max-w-[1400px] px-4 py-20 md:px-8">
    <x-section-heading
        eyebrow="What you can do here"
        title="One marketplace, three ways to play."
        subtitle="Whether you're chasing the Eevee Special Illustration Rare or offloading dupes from your latest booster box, PokeTrade is built for it."
        align="center" />

    <div class="mt-12 grid gap-6 md:grid-cols-3">
        @foreach([
            ['title' => 'Buy & Sell', 'icon' => '◇', 'color' => 'pink', 'body' => 'Browse all 180 Prismatic Evolutions cards with live market prices, listed by you and other trainers. Add to cart, check out securely, done.', 'href' => route('cards.index'), 'cta' => 'Open card shop'],
            ['title' => 'Live Auctions', 'icon' => '✦', 'color' => 'violet', 'body' => 'Bid in real-time on illustration rares and hyper rares. Watchers, bid increments, buy-now options — exactly the ramp-up you need for a chase card.', 'href' => route('auctions.index'), 'cta' => 'See live auctions'],
            ['title' => 'Trade Cards', 'icon' => '⇄', 'color' => 'mint', 'body' => 'Propose trades with other collectors. Offer your dupes, request the cards you want, settle without ever opening your wallet.', 'href' => route('trades.index'), 'cta' => 'Start trading'],
        ] as $f)
            <article class="group relative overflow-hidden rounded-3xl border border-ink-200 bg-white p-7 transition hover:-translate-y-1 hover:shadow-xl">
                <div class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-prism-{{ $f['color'] }}/15 blur-2xl"></div>
                <div class="relative">
                    <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl prism-bg text-xl text-white shadow-md">
                        {{ $f['icon'] }}
                    </div>
                    <h3 class="mt-5 font-display text-2xl font-black text-ink-900">{{ $f['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-ink-700">{{ $f['body'] }}</p>
                    <a href="{{ $f['href'] }}" class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-ink-900 transition group-hover:text-prism-violet">
                        {{ $f['cta'] }}
                        <svg class="h-4 w-4 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/></svg>
                    </a>
                </div>
            </article>
        @endforeach
    </div>
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
            <x-empty-state title="No featured cards yet" message="Run the seeder to populate cards from pokemontcg.io." />
        @endif
    </div>
</section>

{{-- =====================================================
     MERCH STRIP — booster boxes, ETBs, plushies
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
                <x-prism-button :href="route('shop.index')" size="md" class="mt-8 self-start">
                    Shop the merch
                </x-prism-button>
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
                    Sign up to save a wishlist, place bids, propose trades, and roll the gacha. It's free, it's clean, it's prismatic.
                </p>
            </div>
            <div class="flex flex-col gap-3 md:items-end">
                <x-prism-button :href="route('register')" size="lg">
                    Create your account
                </x-prism-button>
                <p class="text-xs text-ink-500">
                    Already a trainer? <a href="{{ route('login') }}" class="font-bold text-ink-900 underline-offset-4 hover:underline">Log in →</a>
                </p>
            </div>
        </div>
    </div>
</section>
@endguest

</x-app-layout>
