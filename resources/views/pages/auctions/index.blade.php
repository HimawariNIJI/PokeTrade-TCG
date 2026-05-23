<x-app-layout>

<section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-ink-900 to-ink-700"></div>
    <div class="absolute inset-0 -z-10 opacity-20" style="background: radial-gradient(closest-side, rgba(180, 107, 255, 0.6), transparent 70%);"></div>
    <div class="absolute inset-x-0 top-0 -z-10 h-px prism-bg"></div>

    <div class="mx-auto max-w-[1400px] px-4 pb-14 pt-16 md:px-8 md:pt-20">
        <span class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-white backdrop-blur">
            <span class="h-2 w-2 animate-pulse rounded-full bg-prism-mint"></span>
            Live now
        </span>
        <h1 class="mt-4 font-display text-5xl font-black tracking-tight text-white md:text-6xl">
            Auction <span class="prism-text">house</span>.
        </h1>
        <p class="mt-3 max-w-2xl text-white/70">Bid in real time on illustration rares and chase cards. Bidding requires login. Highest bid at the timer wins.</p>
    </div>
</section>

@php
    $heroAuction = $highlighted;

    $gridLive = $live->getCollection()
        ->reject(fn ($a) =>
            $heroAuction &&
            $a->is($heroAuction)
        )
        ->values();
@endphp

@if($heroAuction)
    <section class="mx-auto max-w-[1400px] px-4 pt-12 md:px-8">
        <a href="{{ route('auctions.show', $heroAuction) }}"
           x-data="auctionCountdown('{{ $heroAuction->ends_at?->toIso8601String() }}')"
           class="arena-surface group relative block overflow-hidden rounded-[2rem] border border-prism-violet/40 p-6 text-ink-50 shadow-2xl transition hover:border-prism-pink md:p-10">
            <div class="pointer-events-none absolute -left-24 -top-24 h-64 w-64 rounded-full bg-prism-pink/30 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-28 -right-20 h-72 w-72 rounded-full bg-prism-sky/20 blur-3xl"></div>

            <div class="relative grid items-center gap-8 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <div class="relative">
                        <div class="absolute -inset-3 -z-10 rounded-3xl prism-bg opacity-60 blur-2xl"></div>
                        <div class="overflow-hidden rounded-3xl bg-ink-900/60 p-3 ring-1 ring-white/10">
                            @if($heroAuction->card?->image_large || $heroAuction->card?->image_small)
                                <img src="{{ $heroAuction->card?->image_large ?? $heroAuction->card?->image_small }}"
                                     alt="{{ $heroAuction->card?->name }}" class="rounded-2xl">
                            @endif
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-8">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-prism-pink px-3 py-1 text-[11px] font-black uppercase tracking-widest text-ink-900">
                        🔥 Hottest Auction
                    </span>
                    <h2 class="mt-3 font-display text-4xl font-black tracking-tight text-white md:text-5xl">
                        {{ $heroAuction->card?->name }}
                    </h2>
                    <div class="mt-5 flex flex-wrap items-end gap-x-10 gap-y-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/40">Current Bid</p>
                            <p class="animate-bid-counter mt-1 bg-gradient-to-r from-prism-mint to-prism-sky bg-clip-text font-display text-5xl font-black text-transparent">
                                @idr($heroAuction->current_bid)
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/40">Ends In</p>
                            <p class="mt-1 font-mono text-2xl font-bold text-prism-pink" x-text="display">—</p>
                        </div>
                    </div>
                    <span class="mt-6 inline-flex rounded-full bg-gradient-to-r from-prism-pink to-prism-mint px-8 py-3 font-display font-black text-ink-900 shadow-[0_0_24px_-4px] shadow-prism-pink/70 transition group-hover:-translate-y-0.5">
                        Bid on this card ⚡
                    </span>
                </div>
            </div>
        </a>
    </section>
@endif

<section class="mx-auto max-w-[1400px] px-4 py-12 md:px-8">
    <h2 class="mb-6 font-display text-2xl font-black">🔥 Live auctions</h2>

    @if($gridLive->isNotEmpty())
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
            @foreach($gridLive as $a)
                <a href="{{ route('auctions.show', $a) }}" class="group relative block">
                    <div class="prism-halo-glow"></div>
                    <div class="relative overflow-hidden rounded-3xl border border-ink-200 bg-white transition hover:-translate-y-1 hover:border-prism-violet hover:shadow-xl duration-400 ease-[cubic-bezier(.22,1,.36,1)]">
                    <div class="absolute right-3 top-3 z-20 inline-flex items-center gap-1.5 rounded-full bg-rose-600 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-white shadow-lg">
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white"></span>
                        Live
                    </div>
                    <div class="holo-sheen aspect-[245/342] overflow-hidden bg-ink-100">
                        @if($a->card?->image_small)
                            <img src="{{ $a->card->image_small }}" alt="{{ $a->card->name }}" class="h-full w-full object-cover">
                        @endif
                    </div>
                    <div class="relative p-4">
                        <h3 class="line-clamp-1 font-display text-base font-black">{{ $a->card?->name }}</h3>
                        <div class="mt-2 flex items-baseline justify-between">
                            <div>
                                <p class="text-[10px] uppercase tracking-widest text-ink-500">Current bid</p>
                                <p class="font-display text-xl font-black prism-text">@idr($a->current_bid)</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] uppercase tracking-widest text-ink-500">Ends in</p>
                                <p class="font-mono text-sm font-bold text-ink-900">{{ $a->ends_at?->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $live->links() }}</div>
    @else
        <x-empty-state
            icon="✦"
            title="No live auctions right now"
            message="Check back soon — sellers list new auctions throughout the day." />
    @endif

    @if($scheduled->isNotEmpty())
        <h2 class="mb-6 mt-14 font-display text-2xl font-black">⏳ Starting soon</h2>
        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
            @foreach($scheduled as $a)
                <div class="rounded-2xl border border-ink-200 bg-white p-4">
                    <p class="line-clamp-1 font-display text-sm font-bold">{{ $a->card?->name }}</p>
                    <p class="mt-1 text-xs text-ink-500">Starts {{ $a->starts_at?->diffForHumans() }}</p>
                </div>
            @endforeach
        </div>
    @endif

    @if($ended->isNotEmpty())
        <h2 class="mb-6 mt-14 font-display text-2xl font-black">🏁 Recently ended</h2>
        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
            @foreach($ended as $a)
                <div class="flex items-center gap-3 rounded-2xl border border-ink-200 bg-ink-50 p-4">
                    <div class="h-16 w-12 overflow-hidden rounded-md bg-white">
                        @if($a->card?->image_small)
                            <img src="{{ $a->card->image_small }}" alt="" class="h-full w-full object-cover">
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="line-clamp-1 text-sm font-bold">{{ $a->card?->name }}</p>
                        <p class="text-xs text-ink-500">Sold for @idr($a->current_bid)</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

</x-app-layout>
