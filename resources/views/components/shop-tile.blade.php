@props(['item'])

<a href="{{ route('shop.show', $item) }}"
   class="group relative block overflow-hidden rounded-2xl border border-ink-200 bg-white transition hover:border-prism-violet hover:shadow-xl">
    <div class="aspect-square overflow-hidden bg-gradient-to-br from-ink-50 to-ink-100">
        @if($item->image)
            <img src="{{ asset('storage/' . $item->image) }}"
                 alt="{{ $item->name }}"
                 loading="lazy"
                 class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
        @else
            {{-- Fallback: stylised gradient tile with category icon --}}
            <div class="relative flex h-full w-full items-center justify-center halftone">
                <div class="absolute inset-0 prism-bg opacity-20"></div>
                <span class="relative font-display text-3xl font-black text-ink-700/40">
                    {{ strtoupper(substr($item->category, 0, 3)) }}
                </span>
            </div>
        @endif
    </div>

    <div class="p-4">
        <div class="flex items-center gap-2">
            <span class="rounded-full bg-ink-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-ink-700">
                {{ $item->category }}
            </span>
            @if($item->featured)
                <span class="rounded-full prism-bg px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">
                    Hot
                </span>
            @endif
        </div>
        <h3 class="mt-2 line-clamp-2 font-display text-base font-bold text-ink-900">{{ $item->name }}</h3>
        <div class="mt-3 flex items-baseline justify-between">
            <span class="font-display text-xl font-bold text-ink-900">
                @idr($item->price)
            </span>
            <span class="text-xs text-ink-500">
                {{ $item->stock > 0 ? "$item->stock in stock" : 'Sold out' }}
            </span>
        </div>
    </div>
</a>
