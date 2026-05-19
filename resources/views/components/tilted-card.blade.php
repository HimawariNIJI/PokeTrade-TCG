@props([
    'src' => null,
    'alt' => 'Card',
    'rotate' => 15,           // Max tilt amplitude (deg) at the card's edge
    'scaleOnHover' => 1.05,   // Scale-up factor while hovered
    'aspect' => 'aspect-[245/342]',  // Card-shaped by default
    'innerClass' => '',
])

{{-- Mouse-tracked 3D tilt with a cursor-following glare highlight.
     Modelled on the Framer "TiltCard" component: ±15deg tilt,
     1000px perspective, 1.05 scale, 0.2s ease-out, and a white
     screen-blend glare that slides across the card with the cursor. --}}
<figure
    x-data="{
        rx: 0, ry: 0, sc: 1,
        gx: 50, gy: 50, hov: false,
        track(e) {
            const r = this.$el.getBoundingClientRect();
            const ox = ((e.clientX - r.left) / r.width  - 0.5) * 2;
            const oy = ((e.clientY - r.top)  / r.height - 0.5) * 2;
            this.rx = -oy * {{ $rotate }};
            this.ry =  ox * {{ $rotate }};
            this.gx = 50 + ox * 25;
            this.gy = 50 + oy * 25;
        },
        enter() { this.hov = true; this.sc = {{ $scaleOnHover }}; },
        leave() { this.hov = false; this.rx = 0; this.ry = 0; this.sc = 1; this.gx = 50; this.gy = 50; }
    }"
    @mousemove="track($event)"
    @mouseenter="enter()"
    @mouseleave="leave()"
    {{ $attributes->merge(['class' => 'group relative [perspective:1000px]']) }}
>
    <div
        class="relative {{ $aspect }} overflow-hidden rounded-2xl bg-white [transform-style:preserve-3d] transition-transform duration-200 ease-out {{ $innerClass }}"
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

        {{-- Cursor-tracking glare — invisible until hovered, then a
             soft white highlight follows the cursor across the card. --}}
        <div
            class="glare"
            :style="`opacity:${hov ? 1 : 0}; background:radial-gradient(circle at ${gx}% ${gy}%, rgba(255,255,255,.5) 0%, rgba(255,255,255,0) 60%)`"
        ></div>
    </div>
</figure>
