@props(['history'])

{{-- Sparkline of a card's daily tracked market value. Expects a
     collection of CardPriceHistory rows, oldest first. --}}
@php
    $points = collect($history)->values();
    $count = $points->count();
@endphp

@if($count < 2)
    <div class="flex h-40 items-center justify-center rounded-2xl border border-dashed border-ink-200 bg-ink-50 px-6 text-center text-xs text-ink-500">
        Not enough price history yet — daily snapshots will build this chart up over time.
    </div>
@else
    @php
        $prices = $points->map(fn ($p) => (float) $p->market_price);
        $min = $prices->min();
        $max = $prices->max();
        $range = max($max - $min, 1);

        $w = 600; $h = 160; $padY = 18;
        $plotH = $h - 2 * $padY;

        // Per-point data: position in SVG units, date label, formatted price.
        // The tooltip script reads these via data-* attributes.
        $pointData = $points->map(function ($p, $i) use ($count, $w, $h, $padY, $plotH, $min, $range) {
            $x = round($i / ($count - 1) * $w, 2);
            $y = round($padY + (1 - (((float) $p->market_price) - $min) / $range) * $plotH, 2);

            return [
                'x' => $x,
                'y' => $y,
                'xPct' => round($i / ($count - 1) * 100, 4),
                'yPct' => round($y / $h * 100, 4),
                'date' => $p->recorded_at->format('d M Y'),
                'price' => 'Rp ' . number_format((float) $p->market_price, 0, ',', '.'),
                'synthetic' => (bool) $p->is_synthetic,
            ];
        });

        // First real (non-synthetic) snapshot — boundary between the demo
        // backfill and authentic API-sourced history. Null if everything
        // is still synthetic or everything is real.
        $firstRealIndex = $points->search(fn ($p) => ! $p->is_synthetic);
        $allSynthetic = $firstRealIndex === false;
        $hasBoundary = $firstRealIndex !== false && $firstRealIndex > 0;
        $boundaryX = $hasBoundary ? $pointData[$firstRealIndex]['x'] : null;
        $firstRealDate = $hasBoundary ? $points[$firstRealIndex]->recorded_at->format('d M Y') : null;

        $line = $pointData->map(fn ($pt) => $pt['x'] . ',' . $pt['y'])->implode(' ');
        $area = 'M0,' . $h . ' L' . $pointData->map(fn ($pt) => $pt['x'] . ',' . $pt['y'])->implode(' L') . ' L' . $w . ',' . $h . ' Z';

        $rising = (float) $points->last()->market_price >= (float) $points->first()->market_price;
        $stroke = $rising ? '#10b981' : '#f43f5e';
        $gradId = 'pchart-' . uniqid();

        $last = $pointData->last();

        // Width of each hover band, in SVG units. Each band sits centered
        // over a point so the entire chart width is covered.
        $bandW = $w / max($count - 1, 1);
    @endphp

    <figure {{ $attributes }}
            x-data="{
                hover: null,
                show(pt) { this.hover = pt; },
                hide() { this.hover = null; },
            }"
            class="relative">
        <svg viewBox="0 0 {{ $w }} {{ $h }}" class="h-40 w-full" preserveAspectRatio="none"
             role="img" aria-label="Tracked market value over time"
             @mouseleave="hide()">
            <defs>
                <linearGradient id="{{ $gradId }}" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="{{ $stroke }}" stop-opacity="0.26" />
                    <stop offset="100%" stop-color="{{ $stroke }}" stop-opacity="0" />
                </linearGradient>
            </defs>
            {{-- Subtle hatched overlay across the synthetic-data span so it
                 reads as "demo backfill" at a glance. --}}
            @if ($hasBoundary || $allSynthetic)
                <defs>
                    <pattern id="{{ $gradId }}-hatch" patternUnits="userSpaceOnUse" width="6" height="6"
                             patternTransform="rotate(45)">
                        <line x1="0" y1="0" x2="0" y2="6" stroke="#94a3b8" stroke-width="1" stroke-opacity="0.18" />
                    </pattern>
                </defs>
                <rect x="0" y="0"
                      width="{{ $allSynthetic ? $w : $boundaryX }}" height="{{ $h }}"
                      fill="url(#{{ $gradId }}-hatch)" />
            @endif

            <path d="{{ $area }}" fill="url(#{{ $gradId }})" />
            <polyline points="{{ $line }}" fill="none" stroke="{{ $stroke }}" stroke-width="2.5"
                      stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke" />
            <circle cx="{{ $last['x'] }}" cy="{{ $last['y'] }}" r="4" fill="{{ $stroke }}"
                    vector-effect="non-scaling-stroke" />

            {{-- Vertical divider where demo backfill ends and real data starts. --}}
            @if ($hasBoundary)
                <line x1="{{ $boundaryX }}" x2="{{ $boundaryX }}" y1="0" y2="{{ $h }}"
                      stroke="#64748b" stroke-width="1" stroke-dasharray="2 4" stroke-opacity="0.65"
                      vector-effect="non-scaling-stroke" />
            @endif

            {{-- Hovered-point indicator: vertical guideline + marker. --}}
            <template x-if="hover">
                <g>
                    <line :x1="hover.x" :x2="hover.x" y1="0" y2="{{ $h }}"
                          stroke="{{ $stroke }}" stroke-width="1" stroke-dasharray="3 3"
                          stroke-opacity="0.5" vector-effect="non-scaling-stroke" />
                    <circle :cx="hover.x" :cy="hover.y" r="5" fill="white"
                            stroke="{{ $stroke }}" stroke-width="2"
                            vector-effect="non-scaling-stroke" />
                </g>
            </template>

            {{-- Invisible hover bands, one per data point. Wide enough that
                 the cursor doesn't have to land precisely on the line. --}}
            @foreach ($pointData as $i => $pt)
                <rect x="{{ max(0, $pt['x'] - $bandW / 2) }}" y="0"
                      width="{{ $bandW }}" height="{{ $h }}"
                      fill="transparent"
                      class="cursor-crosshair"
                      @mouseenter="show({{ json_encode($pt) }})" />
            @endforeach
        </svg>

        {{-- Floating tooltip, positioned in % so it lines up regardless of
             the SVG's responsive width. --}}
        <div x-show="hover" x-cloak
             x-transition.opacity.duration.100ms
             class="pointer-events-none absolute z-10 -translate-x-1/2 -translate-y-full rounded-lg border border-ink-200 bg-white px-3 py-2 text-center shadow-lg"
             :style="hover && `left: ${hover.xPct}%; top: calc(${hover.yPct}% - 10px);`">
            <p class="font-mono text-[10px] uppercase tracking-widest text-ink-500"
               x-text="hover?.date"></p>
            <p class="font-display text-sm font-black text-ink-900"
               x-text="hover?.price"></p>
            <p x-show="hover?.synthetic" x-cloak
               class="mt-1 inline-block rounded-full bg-amber-100 px-2 py-0.5 font-mono text-[9px] font-bold uppercase tracking-widest text-amber-700">
                demo data
            </p>
        </div>

        @if ($allSynthetic)
            <p class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-center text-[11px] text-amber-700">
                <span class="font-bold">All values shown are demo backfill.</span>
                Real daily snapshots from TCGplayer begin once the price tracker has run.
            </p>
        @elseif ($hasBoundary)
            <p class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-center text-[11px] text-amber-700">
                <span class="font-bold">Demo backfill</span> shown before <span class="font-bold">{{ $firstRealDate }}</span>.
                Authentic snapshots from TCGplayer accrue daily after that.
            </p>
        @endif

        <figcaption class="mt-2 flex justify-between font-mono text-[10px] text-ink-400">
            <span>{{ $points->first()->recorded_at->format('d M Y') }}</span>
            <span>Tracked daily · {{ $count }} snapshots · hover for details</span>
            <span>{{ $points->last()->recorded_at->format('d M Y') }}</span>
        </figcaption>
    </figure>
@endif
