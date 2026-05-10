@props([
    'card' => null,
    'imageUrl' => null,
    'startFlipped' => false,  // true = start showing back; reveal animation
    'autoReveal' => false,     // true = auto-flip to front after a delay
    'revealDelay' => 0,        // ms before auto-flip kicks in
    'class' => '',
])

@php
    $img = $imageUrl ?: ($card?->image_large ?? $card?->image_small);
    $alpineState = $startFlipped ? 'true' : 'false';
@endphp

<div
    x-data="{
        flipped: {{ $alpineState }},
        @if($autoReveal)
        init() {
            setTimeout(() => this.flipped = false, {{ (int) $revealDelay }});
        }
        @endif
    }"
    class="card-flip {{ $class }}"
    @click="flipped = !flipped"
    role="button"
    tabindex="0"
    @keydown.enter.prevent="flipped = !flipped"
    @keydown.space.prevent="flipped = !flipped"
>
    <div class="card-flip-inner aspect-[245/342]" :class="flipped ? 'card-flipped' : ''">
        {{-- FRONT: card art --}}
        <div class="card-face">
            @if($img)
                <img src="{{ $img }}" alt="{{ $card?->name ?? 'Card' }}"
                     class="h-full w-full object-cover" />
            @else
                <div class="flex h-full w-full items-center justify-center bg-ink-100">
                    <span class="font-display text-2xl text-ink-300">No image</span>
                </div>
            @endif
        </div>

        {{-- BACK: PokeTrade card back --}}
        <div class="card-face card-face-back">
            <x-card-back />
        </div>
    </div>
</div>
