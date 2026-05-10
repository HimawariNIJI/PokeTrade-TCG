@props([
    'src' => null,
    'alt' => 'Card',
    'rotate' => 14,           // Max tilt amplitude (deg) at the card's edge
    'scaleOnHover' => 1.05,   // Scale-up factor while hovered
    'aspect' => 'aspect-[245/342]',  // Card-shaped by default
    'innerClass' => '',
])

{{-- Mouse-tracked 3D tilt: as the cursor moves over the card,
     rotateX/rotateY change in real time based on cursor position
     relative to card center. CSS transition smooths the motion
     (poor man's spring — close enough to feel premium). --}}
<figure
    x-data="{
        rx: 0,
        ry: 0,
        sc: 1,
        track(e) {
            const r = this.$el.getBoundingClientRect();
            const ox = e.clientX - r.left - r.width / 2;
            const oy = e.clientY - r.top - r.height / 2;
            this.rx = (oy / (r.height / 2)) * -{{ $rotate }};
            this.ry = (ox / (r.width / 2)) *  {{ $rotate }};
        },
        enter() { this.sc = {{ $scaleOnHover }}; },
        leave() { this.rx = 0; this.ry = 0; this.sc = 1; }
    }"
    @mousemove="track($event)"
    @mouseenter="enter()"
    @mouseleave="leave()"
    {{ $attributes->merge(['class' => 'group relative [perspective:800px]']) }}
>
    <div
        class="holo-sheen relative {{ $aspect }} overflow-hidden rounded-2xl bg-white [transform-style:preserve-3d] transition-transform duration-200 ease-out {{ $innerClass }}"
        :style="`transform: rotateX(${rx}deg) rotateY(${ry}deg) scale(${sc})`"
    >
        @if($src)
            <img src="{{ $src }}" alt="{{ $alt }}"
                 class="block h-full w-full object-cover [transform:translateZ(0)] will-change-transform" />
        @endif

        {{-- Slot lets pages drop badges (Featured / Sold out / Live)
             above the image; they tilt with the card thanks to
             preserve-3d on the parent. --}}
        {{ $slot }}
    </div>
</figure>
