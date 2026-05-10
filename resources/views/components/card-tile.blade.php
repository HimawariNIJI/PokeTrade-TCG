@props(['card'])

{{-- The card image is flippable in place. The title/price block
     below is a separate link to the detail page so clicking the
     card flips it without hijacking navigation. --}}
<div class="group block">
    <div class="relative">
        @if($card->featured)
            <span class="absolute left-3 top-3 z-20 pointer-events-none rounded-full prism-bg px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white shadow-md">
                ★ Featured
            </span>
        @endif

        @if($card->stock <= 0)
            <span class="absolute right-3 top-3 z-20 pointer-events-none rounded-full bg-ink-900/85 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-white">
                Sold out
            </span>
        @elseif($card->stock <= 3)
            <span class="absolute right-3 top-3 z-20 pointer-events-none rounded-full bg-amber-500 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-white">
                Only {{ $card->stock }} left
            </span>
        @endif

        <x-card-flippable :card="$card" class="rounded-2xl overflow-hidden bg-white shadow-md ring-1 ring-ink-100 hover:shadow-xl transition-shadow" />
    </div>

    <a href="{{ route('cards.show', $card) }}" class="mt-3 block px-1">
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
    </a>
</div>
