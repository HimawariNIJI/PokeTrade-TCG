<x-app-layout>

{{-- =====================================================
     GACHA HERO — pull a digital pack, cards land in your
     collection. These are DIGITAL collectibles, not the
     real cards tracked / auctioned elsewhere on the site.
     ===================================================== --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-ink-900 via-prism-violet/20 to-ink-900"></div>
    <div class="absolute inset-0 -z-10 halftone opacity-10"></div>

    <div class="mx-auto grid max-w-[1300px] grid-cols-1 items-center gap-12 px-4 py-20 md:px-8 md:py-28 lg:grid-cols-12">
        <div class="lg:col-span-6">
            <span class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-white backdrop-blur">
                <span class="h-2 w-2 rounded-full bg-prism-gold"></span>
                Digital gacha
            </span>
            <h1 class="mt-4 font-display text-5xl font-black leading-[0.95] text-white md:text-7xl">
                Pull a <span class="prism-text">5-card</span><br/>digital pack.
            </h1>
            <p class="mt-5 max-w-lg text-white/70">
                Tap to pull. Every pull drops 5 random Prismatic Evolutions cards straight into
                <strong class="text-white">your digital collection</strong> — higher rarities drop
                less often, so chase the Special Illustration Rare Eevee ex. These are digital
                collectibles for your binder, not physical cards.
            </p>

            <ul class="mt-8 grid grid-cols-2 gap-3 text-sm">
                @foreach([
                    ['rarity' => 'Common',         'rate' => '60%'],
                    ['rarity' => 'Uncommon',       'rate' => '25%'],
                    ['rarity' => 'Rare',           'rate' => '10%'],
                    ['rarity' => 'Illustration',   'rate' => '4%'],
                    ['rarity' => 'Special Illu.',  'rate' => '0.9%'],
                    ['rarity' => 'Hyper Rare',     'rate' => '0.1%'],
                ] as $r)
                    <li class="flex items-center justify-between rounded-2xl border border-white/20 bg-white/5 px-4 py-2 text-white">
                        <span class="font-semibold">{{ $r['rarity'] }}</span>
                        <span class="font-mono text-xs text-white/70">{{ $r['rate'] }}</span>
                    </li>
                @endforeach
            </ul>

            @auth
                <form method="POST" action="{{ route('gacha.pull') }}" class="mt-10">
                    @csrf
                    <button type="submit" class="group relative inline-flex items-center justify-center gap-3 overflow-hidden rounded-full px-10 py-5 text-base font-bold text-white shadow-2xl transition hover:scale-[1.02]">
                        <span class="absolute inset-0 prism-bg"></span>
                        <span class="relative font-display text-lg font-black uppercase tracking-widest">Pull a pack</span>
                    </button>
                    <p class="mt-3 text-xs text-white/60">
                        Free to pull · cards are added to your collection ·
                        <a href="{{ route('collection.index') }}" class="font-bold text-white underline-offset-4 hover:underline">View my collection →</a>
                    </p>
                </form>
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
                @foreach($previewCards as $i => $card)
                    @php
                        $rot   = [-12, 0, 14][$i] ?? 0;
                        $tx    = [-90, 0, 100][$i] ?? 0;
                        $ty    = [40, 0, 60][$i] ?? 0;
                        $delay = $i * 0.5;
                    @endphp
                    <div class="absolute left-1/2 top-1/2 w-52 md:w-64"
                         style="transform: translate(calc(-50% + {{ $tx }}px), calc(-50% + {{ $ty }}px)) rotate({{ $rot }}deg);">
                        <div class="group relative animate-float" style="animation-delay: {{ $delay }}s;">
                            <div class="prism-halo-glow always-on opacity-50"></div>
                            <x-tilted-card
                                :src="$card->image_large"
                                alt="Preview card"
                                :rotate="14"
                                :scaleOnHover="1.05"
                                innerClass="ring-4 ring-white/30"
                            />
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

</x-app-layout>
