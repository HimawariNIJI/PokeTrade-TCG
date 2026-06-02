<x-app-layout
    description="Track Pokemon TCG Prismatic Evolutions card prices with live market values, bid in real-time auctions, pull a digital gacha, and join the trainer community.">

{{-- =====================================================
     HERO
     ===================================================== --}}
<section class="relative isolate overflow-hidden">
    <div class="absolute inset-0 -z-10 halftone opacity-60"></div>
    <div class="absolute -top-24 right-0 -z-10 hidden h-[120%] w-[55%] md:block" data-parallax="0.12">
        <div class="h-full w-full -rotate-12">
            <div class="absolute inset-y-0 left-0 w-12 prism-bg opacity-25 blur-sm"></div>
            <div class="absolute inset-y-0 left-24 w-6 prism-bg opacity-15"></div>
            <div class="absolute inset-y-0 left-36 w-4 prism-bg opacity-25"></div>
            <div class="absolute inset-y-0 left-44 w-2 prism-bg opacity-40"></div>
        </div>
    </div>
    <div class="pointer-events-none absolute -left-32 top-1/3 -z-10 h-96 w-96 rounded-full bg-prism-pink/20 blur-3xl" data-parallax="0.22"></div>
    <div class="pointer-events-none absolute right-1/3 top-0 -z-10 h-96 w-96 rounded-full bg-prism-mint/20 blur-3xl" data-parallax="-0.18"></div>

    <div class="relative mx-auto grid max-w-[1400px] grid-cols-1 items-center gap-12 px-4 pb-24 pt-16 md:px-8 md:pt-20 lg:grid-cols-12 lg:gap-8 lg:pb-32">
        <div class="lg:col-span-6 xl:col-span-7">
            <h1 class="font-display text-5xl font-black leading-[0.95] tracking-tight md:text-7xl xl:text-[5.75rem]"
                data-reveal="letters">
                <span class="block text-ink-900">Track the</span>
                <span class="prism-text block">Prismatic</span>
                <span class="block text-ink-900">Evolutions.</span>
            </h1>

            <p class="mt-6 max-w-xl text-base leading-relaxed text-ink-700 md:text-lg"
               data-reveal="fade-up" data-reveal-delay="220">
                Eevee and its rainbow of evolutions in <strong>special illustration rares</strong>, hyper rares, and shiny holos — track their market value, pull digital packs, bid in live auctions, and talk shop with the community. Your binder, your prices.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <span data-reveal="pop" style="--reveal-i: 0;" data-reveal-delay="420">
                    <x-prism-button :href="route('cards.index')" size="lg">
                        Track prices
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/></svg>
                    </x-prism-button>
                </span>
                <span data-reveal="pop" style="--reveal-i: 1;" data-reveal-delay="420">
                    <x-prism-button :href="route('auctions.index')" variant="ghost" size="lg">Live auctions</x-prism-button>
                </span>
                <span data-reveal="pop" style="--reveal-i: 2;" data-reveal-delay="420">
                    <x-prism-button :href="route('gacha.index')" variant="outline" size="lg">Pull a pack</x-prism-button>
                </span>
            </div>

            <dl class="mt-10 grid max-w-lg grid-cols-3 gap-8 border-t border-ink-200 pt-8">
                <div data-reveal="fade-up" style="--reveal-i: 0;" data-reveal-delay="600">
                    <dt class="font-display text-3xl font-black prism-text">{{ number_format($totalCards) }}</dt>
                    <dd class="mt-1 text-xs uppercase tracking-widest text-ink-500">cards tracked</dd>
                </div>
                <div data-reveal="fade-up" style="--reveal-i: 1;" data-reveal-delay="600">
                    <dt class="font-display text-3xl font-black prism-text">24/7</dt>
                    <dd class="mt-1 text-xs uppercase tracking-widest text-ink-500">live auctions</dd>
                </div>
                <div data-reveal="fade-up" style="--reveal-i: 2;" data-reveal-delay="600">
                    <dt class="font-display text-3xl font-black prism-text">∞</dt>
                    <dd class="mt-1 text-xs uppercase tracking-widest text-ink-500">gacha pulls</dd>
                </div>
            </dl>
        </div>

        {{-- Hero card stack — fans into place on first scroll-in, then bobs idly.
             Mobile: only the center card renders. The fanned outer pair adds
             visual flair on tablet/desktop but feels cramped on phones. --}}
        <div class="relative lg:col-span-6 xl:col-span-5">
            <div class="relative mx-auto h-[360px] w-full max-w-[520px] md:h-[560px]"
                 data-reveal="spread">
                @foreach($heroCards as $i => $card)
                    @php
                        $rot   = [-9, 4, 12][$i] ?? 0;
                        $tx    = [-90, 0, 90][$i] ?? 0;
                        $ty    = [40, 0, 30][$i] ?? 0;
                        $z     = [10, 30, 20][$i] ?? 10;
                        $delay = $i * 0.6;
                        $fanOrder = [1, 0, 2][$i] ?? $i; // center card lands first, then outer fan
                        $isCenter = $i === 1;
                    @endphp
                    {{-- .reveal-card animates from collapsed-center to the fan position
                         defined by --fx/--fy/--frot. Inner <a> still gets the idle float. --}}
                    <div class="reveal-card absolute left-1/2 top-1/2 w-[230px] md:w-[280px] {{ $isCenter ? '' : 'hidden md:block' }}"
                         style="--fx: {{ $tx }}px; --fy: {{ $ty }}px; --frot: {{ $rot }}deg; --reveal-i: {{ $fanOrder }}; z-index: {{ $z }};">
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

                <div class="pointer-events-none absolute right-0 top-0 hidden h-12 w-12 rounded-tr-2xl border-r-2 border-t-2 border-prism-violet/40 md:block"></div>
                <div class="pointer-events-none absolute bottom-0 left-0 hidden h-12 w-12 rounded-bl-2xl border-b-2 border-l-2 border-prism-pink/40 md:block"></div>
            </div>
        </div>
    </div>

    {{-- Bottom CTA strip — full-width prism ribbon, content centered.
         clip-right wipes the whole rainbow in from the left as you scroll. --}}
    <div class="prism-bg" data-reveal="clip-right">
        <div class="mx-auto grid max-w-[1400px] grid-cols-1 divide-y divide-white/30 text-white md:grid-cols-3 md:divide-x md:divide-y-0">
            @foreach([
                ['route' => 'cards.index', 'label' => 'See full card list'],
                ['route' => 'shop.index',  'label' => 'Booster Boxes & Merch'],
                ['route' => 'about',       'label' => 'About Prismatic Evolutions'],
            ] as $cta)
                <a href="{{ route($cta['route']) }}"
                   class="group flex min-h-[64px] items-center justify-center gap-3 px-6 py-5 text-center font-display text-sm font-bold uppercase tracking-widest transition hover:bg-white/15">
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
    $row3CardOffer = $rowCards->get(3) ?? $rowCards->get(0);
    $row3CardWant  = $rowCards->get(4) ?? $rowCards->get(1);
@endphp

<section class="mx-auto max-w-[1400px] px-4 py-24 md:px-8">
    <div class="max-w-2xl">
        <span class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.3em] text-ink-500"
              data-reveal="pop">
            <span class="h-px w-8 bg-ink-300"></span> How PokeTrade works
        </span>
        <h2 class="mt-4 font-display text-4xl font-black tracking-tight md:text-5xl"
            data-reveal="letters">
            Three ways to play.<br/><span class="prism-text">One prism.</span>
        </h2>
    </div>

    {{-- ROW 1 — PRICE TRACKER: 7/5 split, big card on right --}}
    <article class="mt-16 grid items-center gap-10 lg:grid-cols-12">
        <div class="lg:col-span-7" data-reveal="slide-left">
            <p class="font-mono text-[11px] font-bold uppercase tracking-widest text-ink-500">01 — Price tracker</p>
            <h3 class="mt-2 font-display text-3xl font-black leading-tight md:text-4xl">
                Track every card.<br/>Watch your <em class="prism-text not-italic">grail</em>.
            </h3>
            <p class="mt-4 max-w-md text-ink-700">
                Every Prismatic Evolutions card is browsable with live market value. Sort by rarity, filter by type, and add the cards you want to your <em>chase list</em> to keep an eye on what they're worth — no spreadsheets, no guesswork.
            </p>
            <div class="mt-6 flex flex-wrap gap-x-8 gap-y-3 text-sm">
                <div>
                    <p class="font-mono text-2xl font-black text-ink-900">{{ number_format($totalCards) }}</p>
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

        <div class="relative lg:col-span-5" data-reveal="slide-right">
            <div class="absolute -inset-6 -z-10 rounded-[3rem] prism-bg opacity-20 blur-2xl"></div>
            <div class="mx-auto w-full max-w-[280px]" data-parallax="0.08">
                <div style="transform: rotate(-2deg);">
                    <x-tilted-card
                        :src="$row1Card?->image_large"
                        :alt="$row1Card?->name ?? 'Card'"
                        :rotate="14"
                        :scaleOnHover="1.05"
                    />
                </div>
            </div>
            @if($row1Card)
                <div class="absolute -right-4 -top-4 rounded-2xl bg-white px-3 py-2 shadow-xl ring-1 ring-ink-200"
                     data-reveal="pop" data-reveal-delay="500">
                    <p class="text-[10px] uppercase tracking-widest text-ink-500">Market value</p>
                    <p class="font-display text-lg font-black prism-text">@idr($row1Card->market_price ?: $row1Card->display_price)</p>
                </div>
            @endif
        </div>
    </article>

    {{-- ROW 2 — AUCTIONS: dark slab, real live bids on left, featured lot right --}}
    @php $lot = $featuredAuction; @endphp
    <article class="mt-24 overflow-hidden rounded-3xl bg-ink-900 text-white" data-reveal="fade-up">
        <div class="grid items-center gap-0 lg:grid-cols-12">
            <div class="relative p-8 md:p-12 lg:col-span-6">
                <p class="font-mono text-[11px] font-bold uppercase tracking-widest text-white/60"
                   data-reveal="fade-down">02 — Live auctions</p>
                <h3 class="mt-2 font-display text-3xl font-black leading-tight md:text-4xl"
                    data-reveal="letters">
                    Bid in real time.<br/>Snipe at the <span class="prism-text">final second</span>.
                </h3>

                @if($liveAuctions->isNotEmpty())
                    {{-- Live ticker — real auctions currently running, top bids first --}}
                    <div class="mt-6 space-y-2 rounded-2xl border border-white/10 bg-white/5 p-4"
                         data-reveal="fade-up" data-reveal-delay="300">
                        <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-white/60">
                            <span class="inline-block h-2 w-2 animate-pulse rounded-full bg-rose-500"></span>
                            Live · {{ $liveAuctionCount }} {{ $liveAuctionCount === 1 ? 'auction' : 'auctions' }} running
                        </div>
                        @foreach($liveAuctions as $a)
                            <a href="{{ route('auctions.show', $a) }}" class="flex items-center justify-between border-t border-white/10 pt-2 text-sm transition hover:text-prism-gold">
                                <span class="line-clamp-1 font-semibold">{{ $a->card?->name ?? 'Pokémon card' }}</span>
                                <div class="flex items-center gap-3 text-right">
                                    <span class="font-mono text-xs text-white/60">@ {{ $a->currentLeader?->name ?? 'no bids yet' }}</span>
                                    <span class="font-mono font-bold prism-text">@idr($a->current_bid)</span>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <a href="{{ route('auctions.index') }}" class="mt-6 inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 font-display text-sm font-bold text-ink-900 hover:bg-prism-gold"
                       data-reveal="pop" data-reveal-delay="500">
                        Watch live bids
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/></svg>
                    </a>
                @else
                    {{-- No live auctions — mirror the auctions page empty state --}}
                    <div class="mt-6 rounded-2xl border border-white/10 bg-white/5 p-5"
                         data-reveal="fade-up" data-reveal-delay="300">
                        <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-white/50">
                            <span class="inline-block h-2 w-2 rounded-full bg-white/30"></span> Quiet right now
                        </div>
                        <p class="mt-2 font-display text-lg font-bold">No live auctions right now.</p>
                        <p class="mt-1 text-sm text-white/60">Sellers list new lots throughout the day — check the auction house for what's scheduled next.</p>
                    </div>

                    <a href="{{ route('auctions.index') }}" class="mt-6 inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 font-display text-sm font-bold text-ink-900 hover:bg-prism-gold"
                       data-reveal="pop" data-reveal-delay="500">
                        Visit the auction house
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/></svg>
                    </a>
                @endif
            </div>

            <div class="relative bg-gradient-to-br from-prism-violet/40 via-prism-pink/30 to-prism-sky/40 p-8 md:p-12 lg:col-span-6">
                <div data-reveal="slide-right">
                    <div class="mx-auto w-full max-w-[260px]" data-parallax="0.1">
                        <div style="transform: rotate(3deg);">
                            <x-tilted-card
                                :src="$lot?->card?->image_large ?? $lot?->card?->image_small ?? $row1Card?->image_large"
                                :alt="$lot?->card?->name ?? $row1Card?->name ?? 'Featured card'"
                                :rotate="14"
                                :scaleOnHover="1.05"
                            />
                        </div>
                    </div>
                </div>
                @if($lot)
                    <div class="absolute left-6 top-6 inline-flex items-center gap-1.5 rounded-full bg-rose-600 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-white shadow-lg"
                         data-reveal="pop" data-reveal-delay="650"
                         x-data="auctionCountdown('{{ $lot->ends_at?->toIso8601String() }}')">
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white"></span>
                        Ends in <span class="font-mono" x-text="display">—</span>
                    </div>
                @endif
            </div>
        </div>
    </article>

    {{-- ROW 3 — DIGITAL GACHA: 5/7 split, fanned pull on the left --}}
    <article class="mt-24 grid items-center gap-10 lg:grid-cols-12">
        <div class="relative order-2 lg:order-1 lg:col-span-5">
            <div class="absolute -inset-6 -z-10 rounded-[3rem] prism-bg opacity-20 blur-2xl"></div>
            {{-- Two fanned cards — a mini "pull" preview that spreads on scroll-in --}}
            <div class="relative mx-auto h-[380px] w-full max-w-[400px]"
                 data-reveal="spread">
                <div class="reveal-card absolute left-1/2 top-[70%] w-[170px] sm:w-[220px]"
                     style="--fx: -25%; --fy: -28%; --frot: -9deg; --reveal-i: 0;">
                    <x-tilted-card
                        :src="$row3CardOffer?->image_large"
                        alt="Gacha card"
                        :rotate="14"
                        :scaleOnHover="1.05"
                    />
                </div>
                <div class="reveal-card absolute left-1/2 top-[70%] w-[190px] sm:w-[240px]"
                     style="--fx: 25%; --fy: -28%; --frot: 8deg; --reveal-i: 1;">
                    <x-tilted-card
                        :src="$row3CardWant?->image_large"
                        alt="Gacha card"
                        :rotate="14"
                        :scaleOnHover="1.05"
                    />
                </div>
            </div>
        </div>

        <div class="order-1 lg:order-2 lg:col-span-7" data-reveal="slide-right">
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
    <div class="absolute -right-16 top-12 -z-10 hidden h-72 w-72 md:block" data-parallax="0.25">
        <div class="pokeball-watermark h-full w-full text-prism-violet"></div>
    </div>

    <div class="mx-auto max-w-[1400px] px-4 py-20 md:px-8">
        <div class="flex items-end justify-between gap-6" data-reveal="fade-up">
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
                @foreach($featuredCards as $i => $card)
                    <div data-reveal="tilt-in"
                         style="--reveal-i: {{ $i }}; --tilt-dir: {{ $i % 2 === 0 ? '-1' : '1' }};">
                        <x-card-tile :card="$card" />
                    </div>
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
            <div class="flex flex-col justify-between p-8 md:p-12 lg:col-span-4" data-reveal="slide-left">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest"
                          data-reveal="pop">
                        <span class="h-2 w-2 rounded-full bg-prism-gold"></span> Custom shop
                    </span>
                    <h2 class="mt-4 font-display text-4xl font-black leading-tight md:text-5xl"
                        data-reveal="letters">
                        Boxes,<br/>bundles,<br/><span class="prism-text">plushies</span>.
                    </h2>
                    <p class="mt-4 text-sm text-white/70" data-reveal="fade-up" data-reveal-delay="200">Sealed product, sleeves, playmats, and the merch you actually want — admin-curated, image-uploaded, ready to ship.</p>
                </div>
                <span data-reveal="pop" data-reveal-delay="400">
                    <x-prism-button :href="route('shop.index')" size="md" class="mt-8 self-start">Shop the merch</x-prism-button>
                </span>
            </div>
            <div class="grid grid-cols-2 gap-3 bg-white p-6 md:gap-4 md:p-8 lg:col-span-8 lg:grid-cols-4">
                @foreach($featuredItems as $i => $item)
                    <div data-reveal="fade-up" style="--reveal-i: {{ $i }};">
                        <x-shop-tile :item="$item" />
                    </div>
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
    <div class="relative overflow-hidden rounded-3xl border border-ink-200 bg-white p-10 md:p-16" data-reveal="fade-up">
        <div class="absolute -right-32 -top-32 h-80 w-80 rounded-full prism-bg opacity-20 blur-3xl" data-parallax="0.2"></div>
        <div class="absolute -bottom-32 -left-16 h-80 w-80 rounded-full bg-prism-mint/30 blur-3xl" data-parallax="-0.15"></div>

        <div class="relative grid items-center gap-10 md:grid-cols-2">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-ink-200 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-ink-700"
                      data-reveal="pop" data-reveal-delay="150">
                    Free to join
                </span>
                <h2 class="mt-4 font-display text-3xl font-black tracking-tight md:text-5xl"
                    data-reveal="letters" data-reveal-delay="200">
                    Build your binder.<br/>
                    <span class="prism-text">Track it your way.</span>
                </h2>
                <p class="mt-4 max-w-md text-ink-700" data-reveal="fade-up" data-reveal-delay="400">
                    Sign up to build a chase list, place bids, pull digital packs, and join the community. Free, clean, prismatic.
                </p>
            </div>
            <div class="flex flex-col gap-3 md:items-end" data-reveal="slide-right" data-reveal-delay="500">
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
