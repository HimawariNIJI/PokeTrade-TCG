@props(['card'])

@php
    // Payload handed to the flip overlay. Large image for the
    // big centred card; small as a fallback.
    $flipPayload = [
        'img'  => $card->image_large ?: $card->image_small,
        'name' => $card->name,
    ];
@endphp

<div class="group relative block">
    {{-- Rainbow halo behind the card, fades in on group hover --}}
    <div class="prism-halo-glow"></div>

    {{-- Clicking the card spins it to centre — handled by the
         single <x-card-flip-overlay /> living in the layout. --}}
    <button
        type="button"
        x-data
        x-on:click="$dispatch('flip-card', @js($flipPayload))"
        class="block w-full cursor-pointer text-left"
        aria-label="View {{ $card->name }}"
    >
        <x-tilted-card
            :src="$card->image_small"
            :alt="$card->name"
            :rotate="15"
            :scaleOnHover="1.05"
            innerClass="ring-1 ring-ink-100 shadow-md"
        >
            @if($card->featured)
                <span class="absolute left-3 top-3 z-20 inline-flex items-center gap-1 rounded-full prism-bg-deep px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white shadow-md pointer-events-none [transform:translateZ(20px)]">
                    <span class="sparkle">✦</span> Featured
                </span>
            @endif

            @if($card->stock <= 0)
                <span class="absolute right-3 top-3 z-20 rounded-full bg-ink-900/85 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-white pointer-events-none [transform:translateZ(20px)]">
                    Sold out
                </span>
            @elseif($card->stock <= 3)
                <span class="absolute right-3 top-3 z-20 rounded-full bg-amber-400 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-ink-900 pointer-events-none [transform:translateZ(20px)]">
                    Only {{ $card->stock }} left
                </span>
            @endif
        </x-tilted-card>
    </button>

    <div class="relative mt-3 px-1">
        <div class="flex items-start justify-between gap-2">
            <h3 class="line-clamp-1 font-display text-sm font-bold text-ink-900 group-hover:text-prism-violet transition-colors">{{ $card->name }}</h3>
            <span class="shrink-0 text-xs font-mono text-ink-500">#{{ $card->number ?? '—' }}</span>
        </div>

        <div class="mt-1.5 flex flex-wrap items-center gap-1">
            @foreach(($card->types ?? []) as $type)
                <x-type-chip :type="$type" size="sm" />
            @endforeach
            @if($card->rarity)
                <span class="text-[10px] font-medium uppercase tracking-wider text-ink-500">
                    {{ $card->rarity }}
                </span>
            @endif
        </div>

        <div class="mt-2 flex items-baseline justify-between">
            <span class="font-display text-base font-bold text-ink-900">@idr($card->display_price)</span>
            @if($card->market_price && $card->market_price > 0)
                <span class="text-[10px] text-ink-500">
                    Market <span class="font-mono">@idr($card->market_price)</span>
                </span>
            @endif
        </div>

        {{-- Market Price → price-history page.
             TODO(backend): point at a dedicated price-chart route
             once the price-tracker pivot lands; for now it opens
             the existing card detail page. --}}
        <a
            href="{{ route('cards.show', $card) }}"
            class="group/btn relative mt-3 flex items-center justify-center gap-1.5 overflow-hidden rounded-xl prism-bg-deep px-3 py-2 text-xs font-bold uppercase tracking-wider text-white shadow-[var(--shadow-soft)] transition-transform duration-200 ease-[var(--ease-spring)] hover:-translate-y-0.5 active:scale-[.97]"
        >
            <span class="pointer-events-none absolute inset-x-0 top-0 h-1/2 bg-gradient-to-b from-white/35 to-transparent"></span>
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 3v18h18" />
                <path d="M7 14l4-4 4 3 5-6" />
            </svg>
            Market Price
        </a>
    </div>
</div>
