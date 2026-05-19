@props(['card'])

{{--
    Collection / digital-vault display tile.

    A deliberately stripped-down cousin of <x-card-tile>: it shows ONLY
    the card itself — art, name, rarity, types — with no price, market
    value, stock badges or buy/track buttons. Digital cards pulled from
    the gacha are collectibles, not listings, so the vault is a clean
    display of the cards a trainer has picked up.
--}}
@php
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
        />
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
    </div>
</div>
