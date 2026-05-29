@props(['size' => 'md', 'tagline' => false])

@php
    $textSize = match ($size) {
        'sm' => 'text-base',
        'md' => 'text-xl',
        'lg' => 'text-3xl',
        'xl' => 'text-5xl md:text-6xl',
        default => 'text-xl',
    };
    $iconSize = match ($size) {
        'sm' => 'h-6 w-6',
        'md' => 'h-8 w-8',
        'lg' => 'h-11 w-11',
        'xl' => 'h-14 w-14',
        default => 'h-8 w-8',
    };
@endphp

<a href="{{ route('home') }}" {{ $attributes->merge(['class' => 'group inline-flex items-center gap-2.5']) }}>
    {{-- Prism pokeball: top half rainbow, classic split band + centre
         button. Tilts playfully on hover. --}}
    <span class="relative inline-flex {{ $iconSize }} shrink-0 items-center justify-center overflow-hidden rounded-full shadow-[var(--shadow-soft)] ring-1 ring-ink-900/15 transition-transform duration-300 ease-[var(--ease-spring)] group-hover:-rotate-12">
        <span class="absolute inset-x-0 bottom-0 top-1/2 bg-white"></span>
        <span class="absolute inset-x-0 top-0 h-1/2 prism-bg"></span>
        <span class="absolute inset-x-0 top-1/2 h-[2px] -translate-y-1/2 bg-ink-900"></span>
        <span class="relative h-1/3 w-1/3 rounded-full bg-white shadow-inner ring-[1.5px] ring-ink-900"></span>
    </span>

    <span class="flex flex-col leading-none">
        <span class="font-display font-bold tracking-tight {{ $textSize }}">
            <span class="text-ink-900">Poke</span><span class="prism-text">Trade</span>
        </span>
        @if($tagline)
            <span class="mt-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-ink-500">
                Prismatic Evolutions Edition
            </span>
        @endif
    </span>
</a>
