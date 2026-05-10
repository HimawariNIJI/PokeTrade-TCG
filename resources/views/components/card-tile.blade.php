@props(['card'])

<a href="{{ route('cards.show', $card) }}" class="group relative block">
    {{-- Rainbow halo behind the card, fades in on group hover --}}
    <div class="prism-halo-glow"></div>

    <x-tilted-card
        :src="$card->image_small"
        :alt="$card->name"
        :rotate="14"
        :scaleOnHover="1.05"
        innerClass="ring-1 ring-ink-100 shadow-md"
    >
        @if($card->featured)
            <span class="absolute left-3 top-3 z-20 rounded-full prism-bg px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white shadow-md pointer-events-none [transform:translateZ(20px)]">
                ★ Featured
            </span>
        @endif

        @if($card->stock <= 0)
            <span class="absolute right-3 top-3 z-20 rounded-full bg-ink-900/85 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-white pointer-events-none [transform:translateZ(20px)]">
                Sold out
            </span>
        @elseif($card->stock <= 3)
            <span class="absolute right-3 top-3 z-20 rounded-full bg-amber-500 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-white pointer-events-none [transform:translateZ(20px)]">
                Only {{ $card->stock }} left
            </span>
        @endif
    </x-tilted-card>

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
    </div>
</a>
