<x-app-layout>

{{-- =====================================================
     HERO
     ===================================================== --}}
<section class="relative isolate overflow-hidden">
    <div class="absolute inset-0 -z-10 halftone opacity-50"></div>
    <div class="pointer-events-none absolute -left-32 top-1/3 -z-10 h-96 w-96 rounded-full bg-prism-pink/20 blur-3xl"></div>
    <div class="pointer-events-none absolute right-1/4 top-10 -z-10 h-96 w-96 rounded-full bg-prism-sky/20 blur-3xl"></div>

    <div class="mx-auto max-w-3xl px-4 pb-16 pt-24 text-center md:px-8">
        <span class="inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white/70 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-ink-700 backdrop-blur">
            <span class="inline-block h-2 w-2 rounded-full bg-prism-pink"></span>
            About the project
        </span>
        <h1 class="mt-5 font-display text-5xl font-black leading-[0.95] tracking-tight md:text-7xl">
            <span class="prism-text">PokeTrade</span><br/>is a love letter to <em>Eevee.</em>
        </h1>
        <p class="mt-6 text-base text-ink-700 md:text-lg">
            We built PokeTrade to celebrate <strong>Scarlet &amp; Violet — Prismatic Evolutions</strong>, the set that put Eevee and every one of its evolutions in their own dazzling Special Illustration Rare. A marketplace, an auction house, a trading floor, and a pack-opening floor — all wrapped in the iridescent foil that gave the set its name.
        </p>
    </div>
</section>

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
                Every Eeveelution drawn in their Special Illustration Rare frame — the moment that gave this entire site its reason to exist. Tap any to view the listing.
            </p>
        </div>
        <a href="{{ route('cards.index') }}?rarity=Special%20Illustration%20Rare" class="inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white px-4 py-2 text-sm font-bold text-ink-900 transition hover:border-prism-violet hover:text-prism-violet">
            See every SIR
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/></svg>
        </a>
    </div>

    <div class="grid grid-cols-2 gap-x-5 gap-y-10 sm:grid-cols-3 lg:grid-cols-9 lg:gap-x-3">
        @foreach($eeveelutions as $i => $card)
            @php
                $rot = [-4, 3, -2, 4, -3, 2, -4, 3, -2][$i] ?? 0;
                $type = $card->types[0] ?? 'Colorless';
            @endphp
            <a href="{{ route('cards.show', $card) }}"
               class="group relative block"
               style="transform: rotate({{ $rot }}deg);">
                <div class="absolute -inset-2 -z-10 rounded-2xl prism-bg opacity-0 blur-xl transition-opacity duration-300 group-hover:opacity-40"></div>
                <div class="overflow-hidden rounded-xl bg-white shadow-md ring-1 ring-ink-200/60 transition duration-300 group-hover:-translate-y-2 group-hover:shadow-2xl">
                    <img src="{{ $card->image_small }}" alt="{{ $card->name }}" class="block w-full" loading="lazy" />
                </div>
                <div class="mt-3 flex flex-col items-center gap-1.5 text-center">
                    <p class="font-display text-sm font-black text-ink-900 line-clamp-1">{{ str_replace(' ex', '', $card->name) }}</p>
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
                Most TCG marketplaces look like spreadsheets. Prismatic Evolutions doesn't. Its whole pitch is light bending across a card — so we built the site that way too: rainbow gradients, halftone grit, tilt-on-hover holos, an auction floor that ticks in real time.
            </p>
            <p>
                Under the hood it's <strong class="text-white">Laravel 12 + Tailwind v4</strong>, with cards pulled live from the public pokemontcg.io API and re-priced against our own house markup. Auctions ride websockets. Trades settle when both trainers click confirm. Pack openings shuffle the actual stock list.
            </p>
            <p>
                It started as a four-person student project for an Indonesian higher-ed assignment — backend business logic, infrastructure, the catalog plumbing, and every pixel you see, split four ways. The brief said "TCG marketplace." We answered with a love letter.
            </p>
        </div>
    </div>
</section>

{{-- =====================================================
     THE TEAM — four trainers behind the prism
     ===================================================== --}}
@php
    $team = [
        ['name' => 'Kevin Febrian Setiadi',     'nim' => '0706022410001', 'photo' => 'kevin.jpeg',    'rot' => -3],
        ['name' => 'Caroline Netanya Christianti','nim' => '0706022410041', 'photo' => 'caroline.jpeg', 'rot' => 2],
        ['name' => 'Ethan Cannavaro Lauda',     'nim' => '0706022410002', 'photo' => 'ethan.jpeg',    'rot' => -2],
        ['name' => 'Charlene Athena Tjahjadi',  'nim' => '0706022410012', 'photo' => 'charlene.jpeg', 'rot' => 3],
    ];
@endphp

<section class="relative mx-auto max-w-[1400px] px-4 py-24 md:px-8">
    <div class="mb-14 flex flex-col items-start justify-between gap-4 md:flex-row md:items-end">
        <div class="max-w-xl">
            <p class="font-mono text-[11px] font-bold uppercase tracking-widest text-ink-500">The team</p>
            <h2 class="mt-2 font-display text-4xl font-black tracking-tight md:text-5xl">
                The developer team<br/><span class="prism-text">behind this project.</span>
            </h2>
            <p class="mt-4 text-sm text-ink-700 md:text-base">
                Four students, one binder — every part of PokeTrade was built by the trainers below.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-x-5 gap-y-12 sm:gap-x-8 lg:grid-cols-4 lg:gap-x-6">
        @foreach($team as $member)
            <article class="group relative" style="transform: rotate({{ $member['rot'] }}deg);">
                <div class="absolute -inset-3 -z-10 rounded-3xl prism-bg opacity-0 blur-2xl transition-opacity duration-500 group-hover:opacity-40"></div>

                <div class="relative aspect-[4/5] overflow-hidden rounded-2xl bg-ink-100 shadow-lg ring-1 ring-ink-200 transition duration-500 group-hover:-translate-y-2 group-hover:shadow-2xl">
                    <img src="{{ asset('images/team/' . $member['photo']) }}"
                         alt="{{ $member['name'] }}"
                         class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105"
                         loading="lazy" />

                    {{-- prismatic foil sheen on hover --}}
                    <div class="pointer-events-none absolute inset-0 prism-bg opacity-0 mix-blend-overlay transition-opacity duration-500 group-hover:opacity-30"></div>

                    {{-- bottom gradient + NIM badge --}}
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink-900/90 via-ink-900/40 to-transparent p-4">
                        <p class="font-mono text-[10px] font-bold uppercase tracking-widest text-white/70">NIM</p>
                        <p class="font-mono text-sm font-bold text-white">{{ $member['nim'] }}</p>
                    </div>
                </div>

                <div class="mt-4 px-1">
                    <h3 class="font-display text-lg font-black leading-tight text-ink-900 md:text-xl">
                        {{ $member['name'] }}
                    </h3>
                    <div class="mt-2 inline-flex items-center gap-2">
                        <span class="h-px w-6 prism-bg"></span>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-ink-500">Trainer</span>
                    </div>
                </div>
            </article>
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
                The catalog is live, the auctions are running, and Umbreon ex is somewhere in there waiting to ruin your wallet.
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <x-prism-button :href="route('cards.index')" size="lg">
                    Take me to the cards
                </x-prism-button>
                <x-prism-button :href="route('packs.index')" variant="ghost" size="lg">Open a pack</x-prism-button>
            </div>
        </div>
    </div>
</section>

</x-app-layout>
