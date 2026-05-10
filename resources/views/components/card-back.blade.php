@props(['size' => 'md'])

@php
    $cls = match ($size) {
        'sm' => 'aspect-[245/342]',
        'md' => 'aspect-[245/342]',
        'lg' => 'aspect-[245/342]',
        default => 'aspect-[245/342]',
    };
@endphp

<div {{ $attributes->merge(['class' => "cardback-art $cls flex flex-col items-center justify-center p-6 text-white"]) }}>
    {{-- Diamond brand mark --}}
    <div class="relative inline-flex h-20 w-20 items-center justify-center">
        <span class="absolute inset-0 rotate-45 rounded-md prism-bg opacity-90"></span>
        <span class="absolute inset-[6px] rotate-45 rounded-[5px] bg-[#1a1230]"></span>
        <span class="absolute inset-[12px] rotate-45 rounded-[3px] prism-bg opacity-80"></span>
    </div>

    <p class="relative mt-6 font-display text-2xl font-black tracking-tight">
        Poke<span class="prism-text">Trade</span>
    </p>
    <p class="relative mt-1 text-[9px] font-bold uppercase tracking-[0.3em] text-white/60">
        Prismatic Evolutions
    </p>

    {{-- Bottom hairline --}}
    <span class="relative mt-8 inline-block h-[3px] w-16 prism-bg rounded-full"></span>
</div>
