<x-app-layout>

{{-- =====================================================
     BREADCRUMB + CARD HERO
     ===================================================== --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-ink-50 to-white"></div>
    <div class="absolute inset-x-0 top-0 -z-10 h-[55%] halftone opacity-40"></div>
    <div class="absolute -left-24 top-1/4 -z-10 h-96 w-96 rounded-full bg-prism-pink/15 blur-3xl"></div>
    <div class="absolute -right-24 top-1/4 -z-10 h-96 w-96 rounded-full bg-prism-mint/15 blur-3xl"></div>

    <div class="mx-auto max-w-[1400px] px-4 pb-10 pt-8 md:px-8">
        <nav class="mb-6 text-xs text-ink-500">
            <a href="{{ route('home') }}" class="hover:text-ink-900">Home</a>
            <span class="mx-2">/</span>
            <a href="{{ route('cards.index') }}" class="hover:text-ink-900">Cards</a>
            <span class="mx-2">/</span>
            <span class="text-ink-900">{{ $card->name }}</span>
        </nav>

        <div class="grid gap-12 lg:grid-cols-12">
            {{-- LEFT: Card hero --}}
            <div class="lg:col-span-5 xl:col-span-5">
                <div class="group relative mx-auto max-w-md">
                    <div class="prism-halo-glow always-on opacity-40"></div>
                    <x-tilted-card
                        :src="$card->image_large"
                        :alt="$card->name"
                        :rotate="16"
                        :scaleOnHover="1.04"
                        innerClass="shadow-2xl ring-1 ring-white/60"
                    />
                </div>

                {{-- Set + artist micro-strip --}}
                <div class="mt-6 flex flex-wrap items-center justify-center gap-3 text-xs text-ink-500">
                    <span class="font-mono">{{ $card->set_id }}-{{ $card->number }}</span>
                    <span>·</span>
                    <span>{{ $card->set_name }}</span>
                    @if($card->artist)
                        <span>·</span>
                        <span>Illus. <strong class="text-ink-700">{{ $card->artist }}</strong></span>
                    @endif
                </div>
            </div>

            {{-- RIGHT: Title, price, actions --}}
            <div class="lg:col-span-7 xl:col-span-7">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-ink-900 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-white">
                        {{ $card->supertype }}
                    </span>
                    @if($card->rarity)
                        <span class="rounded-full prism-bg px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-white">
                            {{ $card->rarity }}
                        </span>
                    @endif
                    @if($card->regulation_mark)
                        <span class="rounded-full border border-ink-200 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-ink-700">
                            Regulation {{ $card->regulation_mark }}
                        </span>
                    @endif
                </div>

                <h1 class="mt-4 font-display text-5xl font-black leading-[0.95] tracking-tight md:text-6xl">
                    {{ $card->name }}
                </h1>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    @foreach(($card->types ?? []) as $type)
                        <x-type-chip :type="$type" />
                    @endforeach
                    @if($card->hp)
                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700">
                            HP <span class="font-mono">{{ $card->hp }}</span>
                        </span>
                    @endif
                    @if(!empty($card->subtypes))
                        <span class="text-xs text-ink-500">
                            {{ implode(' · ', $card->subtypes) }}
                        </span>
                    @endif
                </div>

                {{-- Price card --}}
                <div class="mt-8 grid gap-4 rounded-3xl border border-ink-200 bg-white p-6 md:grid-cols-2">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500">Our price</p>
                        <p class="mt-1 font-display text-4xl font-black text-ink-900">
                            @idr($card->display_price)
                        </p>
                        @if($card->market_price && $card->price > 0 && $card->market_price > 0)
                            @php $diff = $card->price - $card->market_price; @endphp
                            <p class="mt-1 text-xs {{ $diff > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ $diff > 0 ? '↑' : '↓' }} @idr(abs($diff)) vs. market
                            </p>
                        @endif
                    </div>

                    <div class="md:border-l md:border-ink-200 md:pl-6">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500">Market value (TCGplayer)</p>
                        <p class="mt-1 font-display text-2xl font-bold text-ink-700">
                            {{ $card->market_price ? 'Rp ' . number_format((float) $card->market_price, 0, ',', '.') : '—' }}
                        </p>
                        <p class="mt-1 text-xs text-ink-500">
                            Stock:
                            @if($card->stock > 0)
                                <span class="font-bold text-emerald-700">{{ $card->stock }} available</span>
                            @else
                                <span class="font-bold text-rose-600">Sold out</span>
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="mt-6 flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('cart.add') }}">
                        @csrf
                        <input type="hidden" name="item_type" value="card">
                        <input type="hidden" name="item_id" value="{{ $card->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <x-prism-button type="submit" size="lg" :disabled="$card->stock <= 0">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                            </svg>
                            {{ $card->stock > 0 ? 'Add to cart' : 'Sold out' }}
                        </x-prism-button>
                    </form>

                    @auth
                        <form method="POST" action="{{ route('wishlist.toggle', $card) }}">
                            @csrf
                            <x-prism-button type="submit" variant="ghost" size="lg">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5A4.69 4.69 0 0 0 12 6.073a4.69 4.69 0 0 0-4.313-2.323C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                                Wishlist
                            </x-prism-button>
                        </form>
                    @else
                        <x-prism-button :href="route('login')" variant="ghost" size="lg">Log in to wishlist</x-prism-button>
                    @endauth
                </div>

                {{-- Flavor text --}}
                @if($card->flavor_text)
                    <blockquote class="mt-8 rounded-2xl border-l-4 border-prism-violet bg-ink-50 p-5 italic text-ink-700">
                        “{{ $card->flavor_text }}”
                    </blockquote>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- =====================================================
     ATTACKS / WEAKNESSES / RESISTANCES — TCG-style panel
     ===================================================== --}}
@if(!empty($card->attacks) || !empty($card->weaknesses) || !empty($card->retreat_cost))
<section class="mx-auto max-w-[1400px] px-4 pb-16 md:px-8">
    <div class="grid gap-6 lg:grid-cols-3">
        {{-- ATTACKS --}}
        @if(!empty($card->attacks))
            <div class="lg:col-span-2">
                <h2 class="mb-3 font-display text-xl font-black text-ink-900">Attacks</h2>
                <div class="space-y-4">
                    @foreach($card->attacks as $attack)
                        <article class="overflow-hidden rounded-2xl border border-ink-200 bg-white">
                            <div class="flex items-center justify-between gap-4 border-b border-ink-100 bg-gradient-to-r from-ink-50 to-white px-5 py-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    @foreach(($attack['cost'] ?? []) as $cost)
                                        <x-type-chip :type="$cost" size="sm" />
                                    @endforeach
                                    <h3 class="font-display text-base font-black text-ink-900">{{ $attack['name'] ?? 'Attack' }}</h3>
                                </div>
                                @if(!empty($attack['damage']))
                                    <span class="font-display text-2xl font-black text-ink-900">{{ $attack['damage'] }}</span>
                                @endif
                            </div>
                            @if(!empty($attack['text']))
                                <p class="px-5 py-3 text-sm leading-relaxed text-ink-700">{{ $attack['text'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- DEFENSE PANEL --}}
        <div class="space-y-4">
            @if(!empty($card->weaknesses))
                <div class="rounded-2xl border border-ink-200 bg-white p-5">
                    <h3 class="text-[10px] font-bold uppercase tracking-widest text-ink-500">Weakness</h3>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        @foreach($card->weaknesses as $w)
                            <x-type-chip :type="$w['type'] ?? 'Normal'" />
                            <span class="font-mono text-sm font-bold text-rose-700">{{ $w['value'] ?? '' }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($card->resistances))
                <div class="rounded-2xl border border-ink-200 bg-white p-5">
                    <h3 class="text-[10px] font-bold uppercase tracking-widest text-ink-500">Resistance</h3>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        @foreach($card->resistances as $r)
                            <x-type-chip :type="$r['type'] ?? 'Normal'" />
                            <span class="font-mono text-sm font-bold text-emerald-700">{{ $r['value'] ?? '' }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($card->retreat_cost))
                <div class="rounded-2xl border border-ink-200 bg-white p-5">
                    <h3 class="text-[10px] font-bold uppercase tracking-widest text-ink-500">Retreat cost</h3>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        @foreach($card->retreat_cost as $c)
                            <x-type-chip :type="$c" size="sm" />
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
@endif

{{-- =====================================================
     RELATED CARDS
     ===================================================== --}}
@if($related->isNotEmpty())
<section class="mx-auto max-w-[1400px] px-4 pb-20 md:px-8">
    <x-section-heading
        eyebrow="Same type"
        title="More cards you might like" />

    <div class="mt-8 grid grid-cols-2 gap-x-5 gap-y-10 md:grid-cols-4">
        @foreach($related as $r)
            <x-card-tile :card="$r" />
        @endforeach
    </div>
</section>
@endif

</x-app-layout>
