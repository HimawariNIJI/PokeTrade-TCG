<x-app-layout
    title="About"
    description="PokeTrade is a Prismatic Evolutions price tracker with official merch, real-card auctions, a digital gacha, and a trainer community.">


{{-- =====================================================
     HERO
     ===================================================== --}}
<x-page-hero
    mon="flareon"
    eyebrow="About the project"
    title='A love letter to <span class="prism-text">Eevee</span>.'
    subtitle="We built PokeTrade to celebrate Scarlet &amp; Violet: Prismatic Evolutions, the set that put Eevee and every one of its evolutions in their own dazzling Special Illustration Rare. A price tracker, a merch shop, an auction house, a digital gacha, and a community, all wrapped in the iridescent foil that gave the set its name." />

{{-- =====================================================
     EEVEELUTION LINEUP — the rainbow itself
     ===================================================== --}}
@if($eeveelutions->isNotEmpty())
<section class="mx-auto max-w-[1400px] px-4 pb-24 md:px-8">
    <div class="mb-10 flex flex-col items-start justify-between gap-4 md:flex-row md:items-end">
        <div class="max-w-xl">
            <p class="font-mono text-[11px] font-bold uppercase tracking-widest text-ink-500">The rainbow line</p>
            <h2 class="mt-2 font-display text-4xl font-black tracking-tight md:text-5xl">
                Nine evolutions.<br/><span class="prism-text">One prism.</span>
            </h2>
            <p class="mt-4 text-sm text-ink-700 md:text-base">
                Every Eeveelution drawn in their Special Illustration Rare frame — the moment that gave this entire site its reason to exist. Tap any to track its market value.
            </p>
        </div>
        <a href="{{ route('cards.index') }}?rarity=Special%20Illustration%20Rare" class="inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white px-4 py-2 text-sm font-bold text-ink-900 transition hover:border-prism-violet hover:text-prism-violet">
            See every SIR
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/></svg>
        </a>
    </div>

    <div class="grid w-full grid-cols-3 gap-x-3 gap-y-6 sm:grid-cols-5 lg:grid-cols-9 lg:gap-x-3">
        @foreach($eeveelutions as $i => $card)
            @php
                $type = $card->types[0] ?? 'Normal';
            @endphp
            <a href="{{ route('cards.show', $card) }}"
               class="group relative block">
                <div class="absolute -inset-1 -z-10 rounded-md prism-bg opacity-0 blur-md transition-opacity duration-300 group-hover:opacity-40"></div>
                <div class="overflow-hidden rounded-md bg-white shadow-sm ring-1 ring-ink-200/60 transition duration-300 group-hover:-translate-y-1 group-hover:shadow-lg">
                    <img src="{{ $card->image_large ?? $card->image_small }}" alt="{{ $card->name }}" class="block w-full" loading="lazy" />
                </div>
                <div class="mt-1.5 flex flex-col items-center gap-1 text-center">
                    <p class="font-display text-[10px] font-black leading-tight text-ink-900 line-clamp-1">{{ str_replace(' ex', '', $card->name) }}</p>
                    <x-type-chip :type="$type" size="sm" />
                </div>
            </a>
        @endforeach
    </div>
</section>
@endif

{{-- =====================================================
     THE STORY — single editorial column, no marketing boxes
     ===================================================== --}}
<section class="relative bg-ink-900 text-white">
    <div class="absolute inset-x-0 top-0 h-px prism-bg"></div>
    <div class="absolute inset-x-0 bottom-0 h-px prism-bg"></div>
    <div class="pointer-events-none absolute -right-20 top-1/4 h-72 w-72 rounded-full bg-prism-violet/40 blur-3xl"></div>
    <div class="pointer-events-none absolute -left-20 bottom-0 h-72 w-72 rounded-full bg-prism-mint/30 blur-3xl"></div>

    <div class="relative mx-auto max-w-3xl px-4 py-24 md:px-8 md:py-32">
        <p class="font-mono text-[11px] font-bold uppercase tracking-widest text-white/60">Why we built it</p>
        <h2 class="mt-3 font-display text-4xl font-black leading-tight md:text-5xl">
            We wanted a card site that <span class="prism-text">felt like the foil</span>.
        </h2>
        <div class="mt-8 space-y-5 text-base leading-relaxed text-white/80 md:text-lg">
            <p>
                Most TCG price trackers look like spreadsheets. Prismatic Evolutions doesn't. Its whole pitch is light bending across a card — so we built the site that way too: rainbow gradients, halftone grit, tilt-on-hover holos, an auction floor that ticks in real time.
            </p>
            <p>
                Under the hood it's <strong class="text-white">Laravel 12 + Tailwind v4</strong>, with cards pulled live from the public pokemontcg.io API and tracked against real market values. Auctions ride websockets. The community talks shop in the forums. The digital gacha shuffles the full set into your collection.
            </p>
            <p>
                It started as a four-person student project for an Indonesian higher-ed assignment — backend business logic, infrastructure, the catalog plumbing, and every pixel you see, split four ways. The brief said "TCG site." We answered with a love letter.
            </p>
        </div>
    </div>
</section>

{{-- =====================================================
     THE TEAM — four trainers behind the prism
     ===================================================== --}}
@php
    $team = [
        [
            'name' => 'Kevin Febrian Setiadi',
            'nim' => '0706022410001',
            'photo' => 'kevin.jpeg',
            'role' => 'Backend',
            'bio' => 'Business logic, models, and the auction & gacha flows that hold the site together.',
        ],
        [
            'name' => 'Caroline Netanya Christianti',
            'nim' => '0706022410041',
            'photo' => 'caroline.jpeg',
            'role' => 'Catalog',
            'bio' => 'Card ingestion from pokemontcg.io, market-value tracking, and the digital gacha engine.',
        ],
        [
            'name' => 'Ethan Cannavaro Lauda',
            'nim' => '0706022410002',
            'photo' => 'ethan.jpeg',
            'role' => 'Infrastructure',
            'bio' => 'Routing, auth, websockets for live bids, and keeping the deploy pipeline running.',
        ],
        [
            'name' => 'Charlene Athena Tjahjadi',
            'nim' => '0706022410012',
            'photo' => 'charlene.jpeg',
            'role' => 'Frontend',
            'bio' => 'Every pixel — Tailwind tokens, prismatic foil treatments, animations, the entire UI.',
        ],
    ];
@endphp

<section class="relative mx-auto max-w-[1200px] px-4 py-16 md:px-8 md:py-20">
    <div class="mb-10 text-center">
        <p class="font-mono text-[11px] font-bold uppercase tracking-widest text-ink-500">The team</p>
        <h2 class="mt-2 font-display text-3xl font-black tracking-tight md:text-4xl">
            The developer team <span class="prism-text">behind this project</span>
        </h2>
        <p class="mx-auto mt-3 max-w-xl text-sm text-ink-700">
            Four trainers — flip a card to read the dossier.
        </p>
    </div>

    <div class="grid grid-cols-4 gap-4 sm:gap-5">
        @foreach($team as $member)
            <div x-data="{ flipped: false }"
                 class="card-flip card-flip--smooth cursor-pointer"
                 @click="flipped = !flipped"
                 role="button"
                 tabindex="0"
                 @keydown.enter.prevent="flipped = !flipped"
                 @keydown.space.prevent="flipped = !flipped"
                 :aria-pressed="flipped">
                <div class="card-flip-inner aspect-[245/342]" :class="flipped ? 'card-flipped' : ''">

                    {{-- FRONT: portrait with name strip --}}
                    <div class="card-face bg-ink-100">
                        <img src="/images/team/{{ $member['photo'] }}?v=2"
                             alt="{{ $member['name'] }}"
                             class="h-full w-full object-cover" />
                        <div class="absolute inset-x-0 bottom-0 z-10 bg-gradient-to-t from-ink-900 via-ink-900/70 to-transparent p-4 pt-12">
                            <p class="font-mono text-[9px] font-bold uppercase tracking-widest text-white/70">{{ $member['role'] }}</p>
                            <h3 class="mt-0.5 font-display text-base font-black leading-tight text-white">
                                {{ $member['name'] }}
                            </h3>
                        </div>
                    </div>

                    {{-- BACK: prism dossier --}}
                    <div class="card-face card-face-back prism-bg flex flex-col p-5 text-white">
                        <div class="absolute inset-0 bg-ink-900/55"></div>
                        <div class="relative flex h-full flex-col">
                            <div class="flex items-center justify-end">
                                <span class="rounded-full bg-white/15 px-2 py-0.5 font-mono text-[9px] font-bold uppercase tracking-widest backdrop-blur">{{ $member['role'] }}</span>
                            </div>

                            <h3 class="mt-4 font-display text-lg font-black leading-tight">
                                {{ $member['name'] }}
                            </h3>

                            <div class="mt-2 inline-flex items-center gap-2">
                                <span class="h-px w-5 bg-white/60"></span>
                                <p class="font-mono text-[11px] font-bold tracking-wider">{{ $member['nim'] }}</p>
                            </div>

                            <p class="mt-4 text-[12px] leading-relaxed text-white/85">
                                {{ $member['bio'] }}
                            </p>

                            <div class="mt-auto flex items-center pt-4 text-[9px] font-bold uppercase tracking-widest text-white/60">
                                <span>PokeTrade · 2026</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @endforeach
    </div>
</section>

{{-- =====================================================
     BY THE NUMBERS — pulled from the live DB
     ===================================================== --}}
<section class="mx-auto max-w-[1400px] px-4 py-24 md:px-8">
    <div class="grid grid-cols-2 gap-x-6 gap-y-10 md:grid-cols-4">
        <div>
            <dt class="font-display text-5xl font-black prism-text md:text-6xl">{{ $totalCards }}</dt>
            <dd class="mt-2 text-xs uppercase tracking-widest text-ink-500">cards live</dd>
            <p class="mt-3 text-sm text-ink-700">Full Prismatic Evolutions set seeded from pokemontcg.io.</p>
        </div>
        <div>
            <dt class="font-display text-5xl font-black prism-text md:text-6xl">{{ $sirCount }}</dt>
            <dd class="mt-2 text-xs uppercase tracking-widest text-ink-500">special illustration rares</dd>
            <p class="mt-3 text-sm text-ink-700">The chase cards — Eevee &amp; all eight evolutions included.</p>
        </div>
        <div>
            <dt class="font-display text-5xl font-black prism-text md:text-6xl">{{ $hyperRareCount }}</dt>
            <dd class="mt-2 text-xs uppercase tracking-widest text-ink-500">hyper rares</dd>
            <p class="mt-3 text-sm text-ink-700">Gold-foil, max-rarity print runs from the set.</p>
        </div>
        <div>
            <dt class="font-display text-5xl font-black prism-text md:text-6xl">{{ $artistCount }}</dt>
            <dd class="mt-2 text-xs uppercase tracking-widest text-ink-500">credited artists</dd>
            <p class="mt-3 text-sm text-ink-700">The illustrators behind every frame in the catalog.</p>
        </div>
    </div>
</section>

{{-- =====================================================
     CTA
     ===================================================== --}}
<section class="mx-auto max-w-[1400px] px-4 pb-28 md:px-8">
    <div class="relative overflow-hidden rounded-3xl border border-ink-200 bg-white p-10 text-center md:p-16">
        <div class="absolute -right-32 -top-32 h-80 w-80 rounded-full prism-bg opacity-20 blur-3xl"></div>
        <div class="absolute -bottom-32 -left-16 h-80 w-80 rounded-full bg-prism-mint/30 blur-3xl"></div>

        <div class="relative mx-auto max-w-2xl">
            <h2 class="font-display text-3xl font-black tracking-tight md:text-5xl">
                Enough about us.<br/>
                <span class="prism-text">Open the binder.</span>
            </h2>
            <p class="mt-4 text-ink-700 md:text-lg">
                The price tracker is live, the auctions are running, and Umbreon ex is somewhere in there waiting to ruin your wallet.
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <x-prism-button :href="route('cards.index')" size="lg">
                    Track the prices
                </x-prism-button>
                <x-prism-button :href="route('gacha.index')" variant="ghost" size="lg">Pull a pack</x-prism-button>
            </div>
        </div>
    </div>
</section>

</x-app-layout>
