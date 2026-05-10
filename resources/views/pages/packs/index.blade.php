<x-app-layout>

<section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-ink-900 via-prism-violet/20 to-ink-900"></div>
    <div class="absolute inset-0 -z-10 halftone opacity-10"></div>

    <div class="mx-auto grid max-w-[1300px] grid-cols-1 items-center gap-12 px-4 py-20 md:px-8 md:py-28 lg:grid-cols-12">
        <div class="lg:col-span-6">
            <span class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-white backdrop-blur">
                <span class="h-2 w-2 rounded-full bg-prism-gold"></span>
                Pack opening
            </span>
            <h1 class="mt-4 font-display text-5xl font-black leading-[0.95] text-white md:text-7xl">
                Open a <span class="prism-text">5-card</span><br/>card pack.
            </h1>
            <p class="mt-5 max-w-lg text-white/70">
                Tap to open. Each pack guarantees 5 random Prismatic Evolutions cards — higher rarities drop less often, so chase the Special Illustration Rare Eevee ex.
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

            <form method="POST" action="{{ route('packs.open') }}" class="mt-10">
                @csrf
                <button type="submit" class="group relative inline-flex items-center justify-center gap-3 overflow-hidden rounded-full px-10 py-5 text-base font-bold text-white shadow-2xl transition hover:scale-[1.02]">
                    <span class="absolute inset-0 prism-bg"></span>
                    <span class="relative font-display text-lg font-black uppercase tracking-widest">Open pack — @idr(49000)</span>
                </button>
                <p class="mt-3 text-xs text-white/60">Sandbox payment. No real charges.</p>
            </form>
        </div>

        {{-- Preview booster cards — fanned out, click any one to flip --}}
        <div class="relative lg:col-span-6">
            <div class="relative mx-auto h-[420px] w-full max-w-md md:h-[500px]">
                @php $previewCards = $preview->take(3); @endphp
                @foreach($previewCards as $i => $card)
                    @php $rot = [-12, 0, 14][$i] ?? 0; $tx = [-90, 0, 100][$i] ?? 0; $ty = [40, 0, 60][$i] ?? 0; @endphp
                    <div class="group absolute left-1/2 top-1/2 w-52 md:w-64"
                         style="transform: translate(calc(-50% + {{ $tx }}px), calc(-50% + {{ $ty }}px)) rotate({{ $rot }}deg);">
                        <div class="relative">
                            <div class="prism-halo-glow always-on opacity-50"></div>
                            <div class="card-surface relative ring-4 ring-white/30">
                                @if($card->image_large)
                                    <img src="{{ $card->image_large }}" alt="" class="aspect-[245/342] w-full object-cover">
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

</x-app-layout>
