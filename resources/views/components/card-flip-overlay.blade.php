{{-- Fullscreen "spin to center" overlay.

     Include this ONCE per page — it lives in the app layout. Any
     card on the page triggers it by dispatching a `flip-card`
     window event carrying { img, name }:

         x-on:click="$dispatch('flip-card', { img: '…', name: '…' })"

     The card spins in showing the classic card back, decelerates
     through ~4.5 turns, and lands big & centred on the card front,
     hoverable with the same tilt/glare as the grid. Click the
     backdrop (anywhere outside the card) to close. --}}
<div
    x-data="cardFlipOverlay()"
    @flip-card.window="launch($event.detail)"
>
    <template x-if="open">
        <div
            class="cf-backdrop is-in fixed inset-0 z-[100] flex items-center justify-center bg-[#140c28]/80 backdrop-blur-sm"
            @click="close()"
            style="perspective: 1000px;"
        >
            <button
                type="button"
                class="absolute right-6 top-6 text-3xl leading-none text-white/70 transition hover:text-white"
                aria-label="Close"
            >&times;</button>

            <div class="group relative" style="width: min(80vw, 360px);" @click.stop>
                <div class="prism-halo-glow" :class="phase === 'settled' ? 'always-on' : ''"></div>

                <div class="relative aspect-[245/342]" style="perspective: 1000px;">
                    <div
                        class="cf-card"
                        x-ref="bigcard"
                        :class="{
                            'is-spinning':   phase === 'spinning',
                            'is-tilt-ready': tiltReady,
                            'is-closing':    phase === 'closing'
                        }"
                        @animationend="onAnimEnd($event)"
                        @mousemove="track($event)"
                        @mouseenter="enter()"
                        @mouseleave="leave()"
                        :style="phase === 'settled'
                                  ? `transform: rotateX(${rx}deg) rotateY(${ry}deg) scale(${sc})`
                                  : ''"
                    >
                        {{-- FRONT: the card itself --}}
                        <div class="cf-face cf-face--front">
                            <img :src="active.img" :alt="active.name" />
                            <div
                                class="glare"
                                x-show="phase === 'settled'"
                                :style="`opacity:${hov ? 1 : 0}; background:radial-gradient(circle at ${gx}% ${gy}%, rgba(255,255,255,.5) 0%, rgba(255,255,255,0) 60%)`"
                            ></div>
                        </div>

                        {{-- BACK: classic card back, shown while spinning --}}
                        <div class="cf-face cf-face--back">
                            <img src="{{ asset('images/card-back.jpg') }}" alt="Card back" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

@once
<script>
    function cardFlipOverlay() {
        return {
            open: false,
            phase: 'idle',                 // spinning | settled | closing
            tiltReady: false,
            active: { img: '', name: '' },
            rx: 0, ry: 0, sc: 1,           // tilt state for the settled card
            gx: 50, gy: 50, hov: false,    // glare position + hover flag

            launch(detail) {
                if (!detail || !detail.img) return;
                this.active = { img: detail.img, name: detail.name || '' };
                this.tiltReady = false;
                this.rx = 0; this.ry = 0; this.sc = 1;
                this.gx = 50; this.gy = 50; this.hov = false;
                this.phase = 'spinning';
                this.open = true;
            },

            onAnimEnd(e) {
                if (e.animationName === 'cf-spin-to-center') {
                    // hand off from keyframe -> Alpine tilt with no visible jump
                    this.phase = 'settled';
                    this.$nextTick(() => { this.tiltReady = true; });
                }
                if (e.animationName === 'cf-pop-out') {
                    this.open = false;
                    this.phase = 'idle';
                }
            },

            close() {
                if (this.phase === 'spinning') return;   // let the spin finish
                this.phase = 'closing';
            },

            track(e) {
                if (this.phase !== 'settled') return;
                const r = this.$refs.bigcard.getBoundingClientRect();
                const ox = ((e.clientX - r.left) / r.width  - 0.5) * 2;
                const oy = ((e.clientY - r.top)  / r.height - 0.5) * 2;
                this.rx = -oy * 15;  this.ry = ox * 15;
                this.gx = 50 + ox * 25;  this.gy = 50 + oy * 25;
            },
            enter() { if (this.phase === 'settled') { this.hov = true; this.sc = 1.05; } },
            leave() { this.hov = false; this.rx = 0; this.ry = 0; this.sc = 1; this.gx = 50; this.gy = 50; },
        };
    }
</script>
@endonce
