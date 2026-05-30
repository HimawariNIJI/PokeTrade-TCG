<x-app-layout
    title="Digital Gacha"
    description="Pull a digital pack and drop 5 random Prismatic Evolutions cards into your collection. Chase the Special Illustration Rares and build your digital binder.">


    {{-- =====================================================
     GACHA HERO — pull a digital pack, cards land in your
     collection. These are DIGITAL collectibles, not the
     real cards tracked / auctioned elsewhere on the site.
     ===================================================== --}}
    <section class="relative isolate overflow-hidden bg-ink-900">
        {{-- Holo banner backdrop (shared with auctions hero), muted so the pull CTA + preview cards stay primary --}}
        <img src="/images/auctions-banner.png" alt="" aria-hidden="true"
             class="absolute inset-0 -z-30 h-full w-full object-cover object-center opacity-25 blur-[2px] saturate-150">
        {{-- Violet color wash + edge vignette to fade the banner into the page --}}
        <div class="absolute inset-0 -z-20"
             style="background: radial-gradient(ellipse at center, rgba(180,107,255,0.28) 0%, rgba(11,12,20,0.85) 70%);"></div>
        <div class="absolute inset-0 -z-10 halftone opacity-10"></div>
        <x-prism-aurora />

        <div
            class="mx-auto grid max-w-[1300px] grid-cols-1 items-center gap-12 px-4 py-20 md:px-8 md:py-28 lg:grid-cols-12">
            <div class="lg:col-span-6">
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-white backdrop-blur">
                    <span class="h-2 w-2 rounded-full bg-prism-gold"></span>
                    Digital gacha
                </span>
                <h1 class="mt-4 font-display text-5xl font-black leading-[0.95] text-white md:text-7xl">
                    Pull a <span class="prism-text">5-card</span><br />digital pack.
                </h1>
                <p class="mt-5 max-w-lg text-white/70">
                    One <strong class="text-white">free pull every day</strong> — after that, spend
                    points earned from the merch store. Every pull drops 5 random Prismatic Evolutions
                    cards straight into <strong class="text-white">your digital collection</strong>,
                    rarity-weighted so the Special Illustration Rare Eevee ex stays a real chase.
                    These are digital collectibles for your binder, not physical cards.
                </p>

                <ul class="mt-8 grid grid-cols-2 gap-3 text-sm">
                    @foreach ($tiers as $r)
                        <li
                            class="flex items-center justify-between rounded-2xl border border-white/20 bg-white/5 px-4 py-2 text-white">
                            <span class="font-semibold">{{ $r['rarity'] }}</span>
                            <span class="font-mono text-xs text-white/70">{{ $r['rate'] }}</span>
                        </li>
                    @endforeach
                </ul>

                @auth
                    <div x-data="{ confirmOpen: false }" class="mt-10">
                        {{-- Confirmation Modal --}}
                        <div x-show="confirmOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" @keydown.escape="confirmOpen = false">
                            <div class="relative w-full max-w-sm rounded-2xl border border-white/20 bg-gradient-to-b from-ink-800 to-ink-900 p-6 shadow-2xl" @click.away="confirmOpen = false">
                                <h3 class="text-lg font-bold text-white">Confirm Pull</h3>
                                <p class="mt-3 text-white/80">
                                    @if ($freePullAvailable)
                                        Claim your <span class="font-bold text-prism-gold">free daily pull</span>? One pack of 5 cards, on the house.
                                    @else
                                        Spend <span class="font-bold text-prism-gold">{{ $pullCost }} points</span> to pull a pack?
                                    @endif
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

                        {{-- Pull Form --}}
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">

                            <button @click="confirmOpen = true" type="button"
                                class="group relative inline-flex items-center justify-center gap-3 overflow-hidden rounded-full px-10 py-5 text-base font-bold text-white shadow-2xl transition hover:scale-[1.02] shrink-0">
                                <span class="absolute inset-0 prism-bg"></span>
                                <span class="relative font-display text-lg font-black uppercase tracking-widest">
                                    {{ $freePullAvailable ? 'Pull free pack' : 'Pull a pack (' . $pullCost . ' Points)' }}
                                </span>
                            </button>

                            @if ($freePullAvailable)
                                <span class="rounded-full bg-prism-gold/20 px-4 py-2 text-sm font-bold text-prism-gold ring-1 ring-prism-gold/40">
                                    Free daily pull ready
                                </span>
                            @else
                                <span class="animate-pulse rounded-full bg-white/20 px-4 py-2 text-sm font-bold text-white">
                                    You have {{ auth()->user()->points }} points
                                </span>
                            @endif

                        </div>

                        <p class="mt-3 text-xs text-white/60">
                            @if ($freePullAvailable)
                                Your free pull resets every day · extra pulls cost {{ $pullCost }} points ·
                            @else
                                Out of free pulls today — earn points by buying from the
                                <a href="{{ route('shop.index') }}" class="font-bold text-white underline-offset-4 hover:underline">merch store</a> ·
                            @endif
                            cards are added to your collection ·
                            <a href="{{ route('collection.index') }}"
                                class="font-bold text-white underline-offset-4 hover:underline">View my collection →</a>
                        </p>
                    </div>
                @else
                    <div class="mt-10">
                        <x-prism-button :href="route('login')" size="lg">Log in to pull a pack</x-prism-button>
                        <p class="mt-3 text-xs text-white/60">Sign in so your pulled cards can be saved to a collection.</p>
                    </div>
                @endauth
            </div>

            {{-- Preview cards — fanned out, holographic float --}}
            <div class="relative lg:col-span-6">
                <div class="relative mx-auto h-[420px] w-full max-w-md md:h-[500px]">
                    @php $previewCards = $preview->take(3); @endphp
                    @foreach ($previewCards as $i => $card)
                        @php
                            $rot = [-12, 0, 14][$i] ?? 0;
                            $tx = [-76, 0, 100][$i] ?? 0;
                            $ty = [40, 0, 60][$i] ?? 0;
                            $delay = $i * 0.5;
                        @endphp
                        <div class="absolute left-1/2 top-1/2 w-52 md:w-64"
                            style="transform: translate(calc(-50% + {{ $tx }}px), calc(-50% + {{ $ty }}px)) rotate({{ $rot }}deg);">
                            <div class="group relative animate-float" style="animation-delay: {{ $delay }}s;">
                                <div class="prism-halo-glow always-on opacity-50"></div>
                                <x-tilted-card :src="$card->image_large" alt="Preview card" :rotate="14" :scaleOnHover="1.05"
                                    innerClass="ring-4 ring-white/30" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

</x-app-layout>
