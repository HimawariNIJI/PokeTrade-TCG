@props(['item'])

<a href="{{ route('shop.show', $item) }}" class="group relative block">
    {{-- Halo bloom behind the tile --}}
    <div class="prism-halo-glow"></div>

    <div class="relative overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-[var(--shadow-soft)] transition-all duration-400 ease-[var(--ease-out-quint)] hover:-translate-y-1.5 hover:border-prism-violet/60 hover:shadow-[var(--shadow-lift)]">
        <div class="holo-sheen aspect-square overflow-hidden bg-gradient-to-br from-ink-50 to-ink-100">
            @if($item->image)
                <img src="{{ asset('storage/' . $item->image) }}"
                     alt="{{ $item->name }}"
                     loading="lazy"
                     class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
            @else
                <div class="relative flex h-full w-full items-center justify-center halftone">
                    <div class="absolute inset-0 prism-bg opacity-20"></div>
                    <span class="relative font-display text-3xl font-black text-ink-700/40">
                        {{ strtoupper(substr($item->category, 0, 3)) }}
                    </span>
                </div>
            @endif
        </div>

        <div class="relative p-4">
            <div class="flex items-center gap-2">
                <span class="rounded-full bg-ink-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-ink-700">
                    {{ $item->category }}
                </span>
                @if($item->featured)
                    <span class="rounded-full prism-bg-deep px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white shadow-sm">
                        Hot
                    </span>
                @endif
            </div>
            <h3 class="mt-2 line-clamp-3 font-display text-sm font-bold leading-tight text-ink-900 sm:line-clamp-2 sm:text-base">
                {{ $item->name }}
            </h3>
            <div class="mt-3 flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between">
                <span class="font-display text-lg font-bold text-ink-900 sm:text-xl">
                    @idr($item->price)
                </span>

                <span class="text-xs text-ink-500">
                    {{ $item->stock > 0 ? "$item->stock in stock" : 'Sold out' }}
                </span>
            </div>
        </div>
    </div>
</a>
