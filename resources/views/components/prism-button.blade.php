@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'solid', // solid | ghost | outline
    'size' => 'md',
])

@php
    $base = 'group relative inline-flex items-center justify-center gap-2 font-display font-bold tracking-wide rounded-full transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-prism-violet focus-visible:ring-offset-2';
    $sz = match ($size) {
        'sm' => 'px-4 py-2 text-sm',
        'md' => 'px-6 py-3 text-sm',
        'lg' => 'px-8 py-4 text-base',
        default => 'px-6 py-3 text-sm',
    };
    $variantCls = match ($variant) {
        'solid'   => 'text-white shadow-lg shadow-prism-violet/30 hover:shadow-prism-violet/50 hover:-translate-y-0.5',
        'ghost'   => 'text-ink-900 bg-white/70 backdrop-blur hover:bg-white border border-ink-200',
        'outline' => 'text-ink-900 border-2 border-ink-900 hover:bg-ink-900 hover:text-white',
        default   => '',
    };
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @else type="{{ $type }}" @endif
    {{ $attributes->merge(['class' => "$base $sz $variantCls"]) }}
>
    @if($variant === 'solid')
        <span class="absolute inset-0 rounded-full prism-bg"></span>
        <span class="absolute inset-[2px] rounded-full bg-ink-900 opacity-0 transition group-hover:opacity-0"></span>
    @endif
    <span class="relative z-10 inline-flex items-center gap-2">{{ $slot }}</span>
</{{ $tag }}>
