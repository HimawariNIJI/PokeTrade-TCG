<x-app-layout>

<section class="relative isolate overflow-hidden">
    <div class="absolute inset-0 -z-10 halftone opacity-50"></div>
    <div class="mx-auto max-w-3xl px-4 pb-12 pt-20 text-center md:px-8">
        <span class="inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white/70 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-ink-700 backdrop-blur">
            About the project
        </span>
        <h1 class="mt-5 font-display text-5xl font-black leading-[0.95] tracking-tight md:text-7xl">
            <span class="prism-text">PokeTrade</span><br/>is a love letter to <em>Eevee.</em>
        </h1>
        <p class="mt-6 text-base text-ink-700 md:text-lg">
            We built PokeTrade to celebrate the <strong>Scarlet &amp; Violet — Prismatic Evolutions</strong> expansion, the set that put Eevee and every one of its evolutions in their own dazzling Special Illustration Rare. This site is a marketplace, an auction house, a trading floor, and a pack-opening floor — all wrapped in the iridescent foil that gave the set its name.
        </p>
    </div>
</section>

<section class="mx-auto max-w-[1100px] px-4 pb-20 md:px-8">
    <div class="grid gap-6 md:grid-cols-2">
        @foreach([
            ['title' => 'The Set', 'body' => 'Released January 17, 2025 in the West, Prismatic Evolutions (sv8pt5) is a special-set capstone for Scarlet & Violet that retraces Eevee\'s evolution line in the rainbow holographic style the set name promises. 180 cards, with multiple Eevee evolutions appearing as Pokémon ex.'],
            ['title' => 'The Marketplace', 'body' => 'Every card on PokeTrade is sourced from the public pokemontcg.io API, with live market prices and our own house-pricing on top. Buy, sell, list for auction, propose a trade, or open card packs. Auctions run on websockets — bids land in real time.'],
            ['title' => 'The Tech', 'body' => 'Laravel 12, Tailwind v4 (CSS-first config, custom prismatic design tokens), Blade components, SQLite for local development, MySQL-friendly for production. Image-uploaded merch in the custom shop, dropdown-selected cards from the API for the card catalog.'],
            ['title' => 'The Team', 'body' => 'A two-person student project with a friend handling backend business logic and the rest of us building scaffolding, architecture, and the entire UI. Submitted as part of an Indonesian higher-education TCG marketplace assignment.'],
        ] as $card)
            <article class="relative overflow-hidden rounded-3xl border border-ink-200 bg-white p-7 transition hover:shadow-lg">
                <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full prism-bg opacity-15 blur-2xl"></div>
                <h2 class="font-display text-2xl font-black text-ink-900">{{ $card['title'] }}</h2>
                <p class="mt-3 text-sm leading-relaxed text-ink-700">{{ $card['body'] }}</p>
            </article>
        @endforeach
    </div>

    <div class="mt-10 flex justify-center">
        <x-prism-button :href="route('cards.index')" size="lg">
            Take me to the cards
        </x-prism-button>
    </div>
</section>

</x-app-layout>
