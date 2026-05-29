@props(['type' => 'Normal', 'size' => 'md'])

@php
    $key = strtolower($type);
    $colors = [
        'grass'     => 'bg-type-grass',
        'fire'      => 'bg-type-fire',
        'water'     => 'bg-type-water',
        'lightning' => 'bg-type-lightning',
        'psychic'   => 'bg-type-psychic',
        'fighting'  => 'bg-type-fighting',
        'darkness'  => 'bg-type-darkness',
        'metal'     => 'bg-type-metal',
        'fairy'     => 'bg-type-fairy',
        'dragon'    => 'bg-type-dragon',
        'normal'    => 'bg-type-normal',
    ];
    $bg = $colors[$key] ?? 'bg-ink-500';
    // Only the genuinely dark type colors carry white text at WCAG AA;
    // the bright ones (grass, fire, water, etc.) take dark ink text.
    $textColor = in_array($key, ['fighting', 'darkness', 'dragon'])
        ? 'text-white'
        : 'text-ink-900';
    $padding = $size === 'sm' ? 'px-2 py-0.5 text-[10px]' : 'px-2.5 py-1 text-xs';
@endphp

<span class="inline-flex items-center gap-1 rounded-full font-bold uppercase tracking-wide {{ $bg }} {{ $textColor }} {{ $padding }} shadow-sm">
    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
    {{ $type }}
</span>
