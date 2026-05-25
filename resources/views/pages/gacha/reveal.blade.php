<x-app-layout>

    {{-- =====================================================
     GACHA REVEAL — the 5 freshly-pulled cards arrive as a
     stacked, pokeball-backed deck. Each tap flips the top
     card face-up with a single spin and shuffles the last
     one to the back; once all five are revealed the deck
     fans out into a side-by-side row.
     ===================================================== --}}
    <section class="relative overflow-hidden bg-ink-900 text-white">
        <div class="absolute inset-0 -z-10 opacity-30"
            style="background: radial-gradient(closest-side, rgba(255, 106, 213, 0.4), transparent 70%);"></div>

        <div class="mx-auto max-w-[1200px] px-4 py-16 md:px-8 md:py-24" x-data="gachaDeck({{ $pulls->count() }})" x-cloak>
            <div class="text-center">
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest backdrop-blur">
                    Pack pulled
                </span>
                <h1 class="mt-4 font-display text-5xl font-black md:text-7xl">
                    <span x-show="phase !== 'spread'">Tap to <span class="prism-text">reveal</span>.</span>
                    <span x-show="phase === 'spread'">Your <span class="prism-text">5 cards</span>.</span>
                </h1>
            </div>

            {{-- ===== THE STAGE — absolute card layer ===== --}}
            <div class="gacha-stage relative mx-auto mt-12 w-full select-none" x-ref="stage"
                :style="`height:${stageH}px`" :class="phase === 'spread' ? '' : 'cursor-pointer'" role="button"
                :tabindex="phase === 'spread' ? -1 : 0"
                :aria-label="phase === 'spread' ? 'All cards revealed' : 'Tap to flip the next card'"
                @click="advance()" @keydown.enter.prevent="advance()" @keydown.space.prevent="advance()">
                {{-- card pile --}}
                @foreach ($pulls as $i => $card)
                    <div class="gacha-card group absolute left-1/2 top-0" :style="cardStyle({{ $i }})">
                        <div class="prism-halo-glow" :class="haloOn({{ $i }}) ? 'always-on' : ''"></div>
                        <div class="gacha-card-inner" :class="flipped[{{ $i }}] ? 'is-flipped' : ''">
                            {{-- BACK: blue pokeball card back --}}
                            <div class="gacha-face gacha-face--back">
                                <img src="{{ asset('images/card-back.jpg') }}" alt="Card back" />
                            </div>
                            {{-- FRONT: the pulled card --}}
                            <div class="gacha-face gacha-face--front">
                                <img src="{{ $card->image_large }}" alt="{{ $card->name }}" />
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- name + rarity tags — un-scaled layer, faded in once spread --}}
                @foreach ($pulls as $i => $card)
                    <div class="gacha-label absolute left-1/2 top-0 text-center"
                        :style="labelStyle({{ $i }})">
                        <p class="line-clamp-1 text-sm font-bold">{{ $card->name }}</p>
                        <p class="text-[10px] uppercase tracking-widest text-white/60">{{ $card->rarity ?? 'Common' }}
                        </p>
                    </div>
                @endforeach
            </div>

            {{-- ===== post-reveal actions ===== --}}
            <div class="mt-12 flex flex-col items-center gap-4" @click.away="confirmOpen = false">
                {{-- Confirmation Modal --}}
                <div x-show="confirmOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" @keydown.escape="confirmOpen = false">
                    <div class="relative w-full max-w-sm rounded-2xl border border-white/20 bg-gradient-to-b from-ink-800 to-ink-900 p-6 shadow-2xl">
                        <h3 class="text-lg font-bold text-white">Confirm Pull</h3>
                        <p class="mt-3 text-white/80">
                            Convert <span class="font-bold text-prism-gold">10 points</span> to pull a pack?
                        </p>
                        <div class="mt-6 flex gap-3">
                            <button @click="confirmOpen = false" type="button"
                                class="flex-1 rounded-lg border border-white/20 px-4 py-2 text-sm font-bold text-white transition hover:bg-white/10">
                                Cancel
                            </button>
                            <form method="POST" action="{{ route('gacha.pull') }}" class="flex-1">
                                @csrf
                                <button type="submit"
                                    class="w-full rounded-lg bg-prism-gold/20 px-4 py-2 text-sm font-bold text-white transition hover:bg-prism-gold/30">
                                    Confirm
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div x-show="phase === 'spread'" x-transition:enter.duration.500ms
                    class="flex flex-wrap justify-center gap-3">
                    <button @click="confirmOpen = true" type="button"
                        class="group relative inline-flex items-center gap-2 overflow-hidden rounded-full px-7 py-3.5 text-base font-bold text-white shadow-xl transition hover:scale-[1.02]">
                        <span class="absolute inset-0 prism-bg"></span>
                        <span class="relative font-display text-sm font-black uppercase tracking-widest">Pull again
                            (10 Points)</span>
                    </button>
                    <x-prism-button :href="route('collection.index')" variant="ghost" size="md">View collection</x-prism-button>
                </div>
                <div x-show="phase === 'spread'" x-transition:enter.duration.500ms
                    class="flex flex-wrap justify-center gap-3">
                    <span class="animate-pulse rounded-full bg-white/20 px-4 py-2 text-sm font-bold text-white">
                        You have {{ auth()->user()->points }} points
                    </span>
                </div>
            </div>
        </div>
    </section>

    @once
        <script>
            /* Stacked-deck reveal for the gacha pull page.
               `step` counts how many cards have been flipped face-up.
               Each card's pile slot (`depth`) is derived from `step`:
                 · the just-flipped card sits on top         (depth 0)
                 · cards not yet revealed queue behind it     (depth 1..n)
                 · already-revealed cards are pushed to the back. */
            function gachaDeck(total) {
                return {
                    total,
                    step: 0, // cards flipped so far
                    phase: 'deck', // 'deck' | 'spread'
                    busy: false, // ignore clicks mid-animation
                    flipped: [], // index -> revealed?
                    deckW: 240,
                    gap: 14,
                    spreadW: 200,
                    stageH: 335,
                    confirmOpen: false,

                    init() {
                        this.flipped = Array(this.total).fill(false);
                        this.$nextTick(() => this.measure());
                        this._onResize = () => this.measure();
                        window.addEventListener('resize', this._onResize);
                    },

                    destroy() {
                        window.removeEventListener('resize', this._onResize);
                    },

                    /* Size the deck off the stage width: a comfortable card
                       for the pile, a smaller one that fits 5 across the row. */
                    measure() {
                        const w = (this.$refs.stage && this.$refs.stage.clientWidth) || 760;
                        this.deckW = Math.max(170, Math.min(290, w * 0.62));
                        this.gap = Math.max(8, Math.min(16, w * 0.022));
                        this.spreadW = Math.min(this.deckW, (w - this.gap * (this.total - 1)) / this.total);
                        this.stageH = this.deckW * 342 / 245;
                    },

                    /* Pile slot for card `i`: 0 = frontmost. */
                    depth(i) {
                        if (this.step === 0) return i;
                        if (i === this.step - 1) return 0; // just flipped, on top
                        if (i >= this.step) return i - this.step + 1; // still face-down, queued
                        return (this.total - this.step + 1) + i; // revealed, shuffled back
                    },

                    advance() {
                        if (this.phase === 'spread' || this.busy || this.step >= this.total) return;
                        this.busy = true;
                        this.flipped[this.step] = true; // spin the next card face-up
                        this.step++;
                        const done = this.step >= this.total;
                        setTimeout(() => {
                            this.busy = false;
                            if (done) this.phase = 'spread';
                        }, 780);
                    },

                    /* Rainbow halo: the tappable top card (a hint) + every
                       card that has been revealed. */
                    haloOn(i) {
                        return this.flipped[i] || (this.phase === 'deck' && this.depth(i) === 0);
                    },

                    cardStyle(i) {
                        if (this.phase === 'spread') {
                            const s = this.spreadW / this.deckW;
                            const x = (i - (this.total - 1) / 2) * (this.spreadW + this.gap);
                            return `width:${this.deckW}px; z-index:${20 + i};` +
                                ` transition-delay:${i * 0.06}s;` +
                                ` transform: translate(calc(-50% + ${x}px), 0) scale(${s});`;
                        }
                        const d = this.depth(i);
                        const sc = 1 - 0.05 * d;
                        const y = d * 20;
                        const rot = d === 0 ? 0 : (i % 2 ? -1 : 1) * Math.min(d, 3) * 2.2;
                        return `width:${this.deckW}px; z-index:${50 - d};` +
                            ` transform: translate(-50%, ${y}px) scale(${sc}) rotate(${rot}deg);`;
                    },

                    labelStyle(i) {
                        const x = (i - (this.total - 1) / 2) * (this.spreadW + this.gap);
                        const y = this.spreadW * 342 / 245 + 14;
                        const shown = this.phase === 'spread' ? 1 : 0;
                        return `width:${this.spreadW}px; opacity:${shown};` +
                            ` transition-delay:${i * 0.06}s;` +
                            ` transform: translate(calc(-50% + ${x}px), ${y}px);`;
                    },
                };
            }
        </script>
    @endonce

</x-app-layout>
