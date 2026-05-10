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
        'sm' => 'h-5 w-5',
        'md' => 'h-7 w-7',
        'lg' => 'h-10 w-10',
        'xl' => 'h-14 w-14',
        default => 'h-7 w-7',
    };
@endphp

<a href="{{ route('home') }}" {{ $attributes->merge(['class' => 'group inline-flex items-center gap-2.5']) }}>
    {{-- Diamond / prism icon --}}
    <span class="relative inline-flex {{ $iconSize }} items-center justify-center">
        <span class="absolute inset-0 rotate-45 rounded-md prism-bg"></span>
        <span class="absolute inset-[3px] rotate-45 rounded-[5px] bg-white"></span>
        <span class="absolute inset-[6px] rotate-45 rounded-[3px] prism-bg opacity-90"></span>
    </span>

    <span class="flex flex-col leading-none">
        <span class="font-display font-black tracking-tight {{ $textSize }}">
            <span class="text-ink-900">Poke</span><span class="prism-text">Trade</span>
        </span>
        @if($tagline)
            <span class="mt-1 text-[10px] font-medium uppercase tracking-[0.2em] text-ink-500">
                Prismatic Evolutions Edition
            </span>
        @endif
    </span>
</a>
