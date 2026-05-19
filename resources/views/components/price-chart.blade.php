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

        $coords = $points->map(function ($p, $i) use ($count, $w, $padY, $plotH, $min, $range) {
            $x = round($i / ($count - 1) * $w, 2);
            $y = round($padY + (1 - (((float) $p->market_price) - $min) / $range) * $plotH, 2);

            return $x . ',' . $y;
        });

        $line = $coords->implode(' ');
        $area = 'M0,' . $h . ' L' . $coords->implode(' L') . ' L' . $w . ',' . $h . ' Z';

        $rising = (float) $points->last()->market_price >= (float) $points->first()->market_price;
        $stroke = $rising ? '#10b981' : '#f43f5e';
        $gradId = 'pchart-' . uniqid();

        [$lastX, $lastY] = explode(',', $coords->last());
    @endphp

    <figure {{ $attributes }}>
        <svg viewBox="0 0 {{ $w }} {{ $h }}" class="h-40 w-full" preserveAspectRatio="none"
             role="img" aria-label="Tracked market value over time">
            <defs>
                <linearGradient id="{{ $gradId }}" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="{{ $stroke }}" stop-opacity="0.26" />
                    <stop offset="100%" stop-color="{{ $stroke }}" stop-opacity="0" />
                </linearGradient>
            </defs>
            <path d="{{ $area }}" fill="url(#{{ $gradId }})" />
            <polyline points="{{ $line }}" fill="none" stroke="{{ $stroke }}" stroke-width="2.5"
                      stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke" />
            <circle cx="{{ $lastX }}" cy="{{ $lastY }}" r="4" fill="{{ $stroke }}"
                    vector-effect="non-scaling-stroke" />
        </svg>
        <figcaption class="mt-2 flex justify-between font-mono text-[10px] text-ink-400">
            <span>{{ $points->first()->recorded_at->format('d M Y') }}</span>
            <span>Tracked daily · {{ $count }} snapshots</span>
            <span>{{ $points->last()->recorded_at->format('d M Y') }}</span>
        </figcaption>
    </figure>
@endif
