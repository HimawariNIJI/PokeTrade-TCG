@props(['card'])

<a href="{{ route('cards.show', $card) }}"
   class="group relative block">
    {{-- Holographic glow halo (sits behind the card) --}}
    <div class="pointer-events-none absolute -inset-3 rounded-3xl prism-bg opacity-0 blur-2xl transition duration-500 group-hover:opacity-60"></div>

    <div class="card-tilt holo-sheen relative overflow-hidden rounded-2xl bg-white">
        <div class="aspect-[245/342] overflow-hidden bg-gradient-to-br from-ink-100 to-ink-200">
            @if($card->image_small)
                <img src="{{ $card->image_small }}"
                     alt="{{ $card->name }}"
                     loading="lazy"
                     class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
            @else
                <div class="flex h-full w-full items-center justify-center">
                    <span class="font-display text-2xl text-ink-300">No image</span>
                </div>
            @endif
        </div>

        @if($card->featured)
            <span class="absolute left-3 top-3 rounded-full prism-bg px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white shadow-md">
                ★ Featured
            </span>
        @endif

        @if($card->stock <= 0)
            <span class="absolute right-3 top-3 rounded-full bg-ink-900/85 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-white">
                Sold out
            </span>
        @elseif($card->stock <= 3)
            <span class="absolute right-3 top-3 rounded-full bg-amber-500 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-white">
                Only {{ $card->stock }} left
            </span>
        @endif
    </div>

    <div class="mt-3 px-1">
        <div class="flex items-start justify-between gap-2">
            <h3 class="line-clamp-1 font-display text-sm font-bold text-ink-900">{{ $card->name }}</h3>
            <span class="shrink-0 text-xs font-mono text-ink-500">#{{ $card->number ?? '—' }}</span>
        </div>

        <div class="mt-1.5 flex items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-1">
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

        <div class="mt-2 flex items-baseline justify-between">
            <span class="font-display text-lg font-bold text-ink-900">
                ${{ number_format((float) $card->display_price, 2) }}
            </span>
            @if($card->market_price && $card->market_price > 0 && $card->market_price != $card->price)
                <span class="text-[11px] text-ink-500">
                    Market <span class="font-mono">${{ number_format((float) $card->market_price, 2) }}</span>
                </span>
            @endif
        </div>
    </div>
</a>
