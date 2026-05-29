<x-app-layout
    description="Track Pokemon TCG Prismatic Evolutions card prices with live market values, bid in real-time auctions, pull a digital gacha, and join the trainer community.">

{{-- =====================================================
     HERO
     ===================================================== --}}
<section class="relative isolate overflow-hidden bg-white" x-data="heroParallax()" @mousemove.window="track($event)">
    {{-- Soft prism colour washes — white-dominant, just the brand colours --}}
    <div class="pointer-events-none absolute -left-40 -top-20 -z-10 h-[40rem] w-[40rem] rounded-full bg-prism-pink/15 blur-3xl"></div>
    <div class="pointer-events-none absolute right-0 top-1/4 -z-10 h-[34rem] w-[34rem] rounded-full bg-prism-sky/15 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 left-1/3 -z-10 h-[30rem] w-[30rem] rounded-full bg-prism-violet/10 blur-3xl"></div>

    {{-- Real Eevee-evolution artwork floating as parallax depth layers.
         Outer wrapper = mouse parallax, inner img = idle float. Hidden on
         small screens to keep mobile clean. --}}
    @php
        $eevee = [
            ['flareon',  'left-[3%] top-[12%]',     'w-20', 3.0],
            ['jolteon',  'left-[46%] top-[1%]',     'w-16', 1.4],
            ['eevee',    'left-[1%] bottom-[8%]',   'w-28', 2.2],
            ['vaporeon', 'right-[1%] top-[4%]',     'w-20', 1.8],
            ['sylveon',  'right-[2%] bottom-[10%]', 'w-24', 2.6],
        ];
    @endphp
    @foreach($eevee as $i => [$mon, $pos, $size, $depth])
        <div class="pointer-events-none absolute {{ $pos }} z-0 hidden lg:block" aria-hidden="true"
             :style="`transform: translate(${px * -{{ $depth }}}px, ${py * -{{ $depth }}}px)`">
            <img src="{{ asset('images/eevee/' . $mon . '.png') }}" alt=""
                 class="{{ $size }} animate-float drop-shadow-xl" style="animation-delay: {{ $i * 0.6 }}s" />
        </div>
    @endforeach

    <div class="relative z-10 mx-auto grid max-w-[1400px] grid-cols-1 items-center gap-12 px-4 pb-16 pt-14 md:px-8 md:pb-24 md:pt-20 lg:grid-cols-12 lg:gap-6">
        {{-- LEFT --}}
        <div class="lg:col-span-6">
            <div class="enter inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white/80 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-ink-700 shadow-sm backdrop-blur" style="--d:0">
                <span class="inline-block h-2 w-2 animate-pulse rounded-full bg-prism-mint"></span>
                Scarlet &amp; Violet · sv8pt5 · 180 cards live
            </div>

            <h1 class="enter mt-6 font-display text-6xl font-bold leading-[0.9] tracking-tight md:text-7xl xl:text-[5.5rem]" style="--d:90">
                <span class="block text-ink-900">Track the</span>
                <span class="block prism-text">Prismatic</span>
                <span class="block text-ink-900">Evolutions.</span>
            </h1>

            <p class="enter mt-6 max-w-xl text-lg leading-relaxed text-ink-700" style="--d:170">
                Live market prices, real-card auctions, a digital gacha, and a trainer community. Every Eevee evolution, fully holographic.
            </p>

            <div class="enter mt-8 flex flex-wrap gap-3" style="--d:250">
                <x-prism-button :href="route('cards.index')" size="lg">
                    Track prices
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/></svg>
                </x-prism-button>
                <x-prism-button :href="route('gacha.index')" variant="gold" size="lg">Pull a pack</x-prism-button>
                <x-prism-button :href="route('auctions.index')" variant="ghost" size="lg">Live auctions</x-prism-button>
            </div>

            <dl class="enter mt-10 grid max-w-lg grid-cols-3 gap-3" style="--d:330">
                @foreach([['180', 'cards tracked'], ['24/7', 'live auctions'], ['∞', 'gacha pulls']] as [$n, $l])
                    <div class="rounded-2xl border border-ink-200 bg-white px-4 py-3 shadow-[var(--shadow-soft)]">
                        <dt class="font-display text-3xl font-bold prism-text">{{ $n }}</dt>
                        <dd class="mt-1 text-[11px] uppercase tracking-widest text-ink-500">{{ $l }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        {{-- RIGHT: fanned holographic card showcase with mouse parallax --}}
        <div class="enter-pop relative lg:col-span-6" style="--d:220">
            <div class="relative mx-auto h-[420px] w-full max-w-[560px] md:h-[600px]">
                @php $heroCards = $featuredCards->take(3); @endphp
                @foreach($heroCards as $i => $card)
                    @php
                        $cfg = [['-15deg', '-130px', '28px', 10, 1.4], ['7deg', '6px', '-14px', 30, 1.0], ['17deg', '150px', '46px', 20, 0.65]][$i]
                            ?? ['0deg', '0px', '0px', 10, 1.0];
                    @endphp
                    <a href="{{ route('cards.show', $card) }}"
                       class="group absolute left-1/2 top-1/2 w-[200px] animate-float md:w-[280px]"
                       style="transform: translate(calc(-50% + {{ $cfg[1] }}), calc(-50% + {{ $cfg[2] }})) rotate({{ $cfg[0] }}); z-index: {{ $cfg[3] }}; animation-delay: {{ $i * 0.7 }}s"
                       :style="`--px:${px * {{ $cfg[4] }}}px; --py:${py * {{ $cfg[4] }}}px`">
                        <div class="prism-halo-glow always-on" style="opacity:.55"></div>
                        <div class="holo-foil relative overflow-hidden rounded-2xl shadow-2xl ring-1 ring-ink-100 transition-transform duration-300 ease-out group-hover:scale-[1.04]"
                             style="transform: translate3d(var(--px,0), var(--py,0), 0)">
                            <img src="{{ $card->image_large ?? $card->image_small }}" alt="{{ $card->name }}"
                                 class="block aspect-[245/342] w-full object-cover" />
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Bottom quick-links ribbon — white, prism top seam + prism hover --}}
    <div class="relative border-t border-ink-100 bg-white">
        <div class="absolute inset-x-0 top-0 h-[3px] prism-bg"></div>
        <div class="mx-auto grid max-w-[1400px] grid-cols-1 divide-y divide-ink-100 md:grid-cols-3 md:divide-x md:divide-y-0">
            @foreach([
                ['route' => 'cards.index', 'label' => 'See full card list'],
                ['route' => 'shop.index',  'label' => 'Booster Boxes & Merch'],
                ['route' => 'about',       'label' => 'About Prismatic Evolutions'],
            ] as $cta)
                <a href="{{ route($cta['route']) }}"
                   class="group flex min-h-[64px] items-center justify-center gap-3 px-6 py-5 text-center font-display text-sm font-bold uppercase tracking-widest text-ink-700 transition hover:bg-ink-50 hover:text-prism-violet">
                    <span class="line-clamp-1">{{ $cta['label'] }}</span>
                    <svg class="h-4 w-4 shrink-0 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/>
                    </svg>
                </a>
            @endforeach
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
            Three ways to play.<br/><span class="prism-text">One prism.</span>
        </h2>
    </div>

    {{-- ROW 1 — PRICE TRACKER: 7/5 split, big card on right --}}
    <article class="reveal mt-16 grid items-center gap-10 lg:grid-cols-12">
        <div class="lg:col-span-7">
            <p class="font-mono text-[11px] font-bold uppercase tracking-widest text-ink-500">01 — Price tracker</p>
            <h3 class="mt-2 font-display text-3xl font-black leading-tight md:text-4xl">
                Track every card.<br/>Watch your <em class="prism-text not-italic">grail</em>.
            </h3>
            <p class="mt-4 max-w-md text-ink-700">
                Every Prismatic Evolutions card is browsable with live market value. Sort by rarity, filter by type, and add the cards you want to your <em>chase list</em> to keep an eye on what they're worth — no spreadsheets, no guesswork.
            </p>
            <div class="mt-6 flex flex-wrap gap-x-8 gap-y-3 text-sm">
                <div>
                    <p class="font-mono text-2xl font-black text-ink-900">180</p>
                    <p class="text-xs text-ink-500">cards tracked</p>
                </div>
                <div>
                    <p class="font-mono text-2xl font-black text-ink-900">@idr(5000)–@idr(3500000)</p>
                    <p class="text-xs text-ink-500">market value range</p>
                </div>
                <div>
                    <p class="font-mono text-2xl font-black text-ink-900">Live</p>
                    <p class="text-xs text-ink-500">market data</p>
                </div>
            </div>
            <a href="{{ route('cards.index') }}" class="mt-8 inline-flex items-center gap-2 font-display text-sm font-bold text-ink-900 hover:text-prism-violet">
                Open the price tracker
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
                    <p class="text-[10px] uppercase tracking-widest text-ink-500">Market value</p>
                    <p class="font-display text-lg font-black prism-text">@idr($row1Card->market_price ?: $row1Card->display_price)</p>
                </div>
            @endif
        </div>
    </article>

    {{-- ROW 2 — AUCTIONS: dark slab, ticker on left, copy right --}}
    <article class="reveal mt-24 overflow-hidden rounded-3xl bg-ink-900 text-white">
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

    {{-- ROW 3 — DIGITAL GACHA: 5/7 split, fanned pull on the left --}}
    <article class="reveal mt-24 grid items-center gap-10 lg:grid-cols-12">
        <div class="relative order-2 lg:order-1 lg:col-span-5">
            <div class="absolute -inset-6 -z-10 rounded-[3rem] prism-bg opacity-20 blur-2xl"></div>
            {{-- Two fanned cards — a mini "pull" preview --}}
            <div class="relative mx-auto h-[300px] w-full max-w-[340px]">
                <div class="absolute left-1/2 top-1/2 w-[180px] -translate-x-[80%] -translate-y-1/2"
                     style="transform: translate(-80%, -50%) rotate(-9deg);">
                    <x-tilted-card
                        :src="$row3CardOffer?->image_large"
                        alt="Gacha card"
                        :rotate="14"
                        :scaleOnHover="1.05"
                    />
                </div>
                <div class="absolute left-1/2 top-1/2 w-[200px] -translate-x-[10%] -translate-y-1/2"
                     style="transform: translate(-10%, -50%) rotate(8deg);">
                    <x-tilted-card
                        :src="$row3CardWant?->image_large"
                        alt="Gacha card"
                        :rotate="14"
                        :scaleOnHover="1.05"
                    />
                </div>
            </div>
        </div>

        <div class="order-1 lg:order-2 lg:col-span-7">
            <p class="font-mono text-[11px] font-bold uppercase tracking-widest text-ink-500">03 — Digital gacha</p>
            <h3 class="mt-2 font-display text-3xl font-black leading-tight md:text-4xl">
                Pull a pack.<br/>Build a <em class="prism-text not-italic">digital binder</em>.
            </h3>
            <p class="mt-4 max-w-md text-ink-700">
                Pull a digital pack and 5 random Prismatic Evolutions cards drop straight into your collection. Chase the Special Illustration Rares, watch your rarity stats climb — pure collecting, no checkout.
            </p>
            <div class="mt-6 flex flex-wrap gap-x-8 gap-y-3 text-sm">
                <div>
                    <p class="font-mono text-2xl font-black text-ink-900">5</p>
                    <p class="text-xs text-ink-500">cards per pull</p>
                </div>
                <div>
                    <p class="font-mono text-2xl font-black text-ink-900">Free</p>
                    <p class="text-xs text-ink-500">to pull</p>
                </div>
                <div>
                    <p class="font-mono text-2xl font-black text-ink-900">∞</p>
                    <p class="text-xs text-ink-500">collection slots</p>
                </div>
            </div>
            <a href="{{ route('gacha.index') }}" class="mt-8 inline-flex items-center gap-2 font-display text-sm font-bold text-ink-900 hover:text-prism-violet">
                Pull your first pack
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/></svg>
            </a>
        </div>
    </article>
</section>

{{-- =====================================================
     FEATURED CARDS — the hot drops grid
     ===================================================== --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-x-0 top-0 -z-10 h-1/2 bg-gradient-to-b from-ink-50 to-transparent"></div>
    <div class="pokeball-watermark -right-16 top-12 -z-10 hidden h-72 w-72 text-prism-violet md:block"></div>

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
                    <span class="prism-text">Track it your way.</span>
                </h2>
                <p class="mt-4 max-w-md text-ink-700">
                    Sign up to build a chase list, place bids, pull digital packs, and join the community. Free, clean, prismatic.
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
