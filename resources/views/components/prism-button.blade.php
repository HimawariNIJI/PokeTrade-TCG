@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'solid', // solid | ghost | outline | gold
    'size' => 'md',
])

@php
    $base = 'group relative inline-flex items-center justify-center gap-2 font-display font-bold tracking-wide rounded-full cursor-pointer select-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-prism-violet focus-visible:ring-offset-2';

    $sz = match ($size) {
        'sm' => 'px-4 py-2 text-sm',
        'md' => 'px-6 py-3 text-sm',
        'lg' => 'px-8 py-4 text-base',
        default => 'px-6 py-3 text-sm',
    };

    // Tactile variants carry their own gloss + depth + press response.
    $variantCls = match ($variant) {
        'solid'   => 'btn-tactile prism-bg-deep text-white',
        'gold'    => 'btn-tactile text-ink-900',
        'ghost'   => 'btn-tactile bg-white text-ink-900 border border-ink-200 hover:text-prism-violet',
        'outline' => 'text-ink-900 border-2 border-ink-900 transition-all duration-200 ease-out hover:bg-ink-900 hover:text-white active:scale-95',
        default   => '',
    };

    $style = $variant === 'gold'
        ? 'background:linear-gradient(120deg,#ffe08a 0%,#ffd86b 45%,#f7b733 100%);'
        : '';

    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @else type="{{ $type }}" @endif
    @if($style) style="{{ $style }}" @endif
    {{ $attributes->merge(['class' => "$base $sz $variantCls"]) }}
>
    <span class="relative z-10 inline-flex items-center gap-2">{{ $slot }}</span>
</{{ $tag }}>
