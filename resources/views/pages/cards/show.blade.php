<x-app-layout>

@php
    // Display helpers shared across the page.
    $rp = fn ($v) => $v !== null && $v !== ''
        ? 'Rp ' . number_format((float) $v, 0, ',', '.')
        : '—';
    $changeClass = fn ($c) => $c === null
        ? 'text-ink-400'
        : ($c > 0 ? 'text-emerald-600' : ($c < 0 ? 'text-rose-600' : 'text-ink-500'));
    $changeText = fn ($c) => $c === null
        ? '—'
        : ($c > 0 ? '+' : '') . number_format($c, 1) . '%';

    $regNote = match ($card->regulation_mark) {
        'H', 'I', 'J' => 'Legal in the current Standard format (2026 rotation).',
        null, ''      => 'Basic Energy — always Standard-legal.',
        default       => 'Rotated out of the current Standard format.',
    };

    // Evolution line, ordered pre-evolution → this card → evolutions.
    $evoStages = collect();
    if ($evolvesFrom) {
        $evoStages->push(['name' => $evolvesFrom->name, 'card' => $evolvesFrom, 'current' => false]);
    } elseif ($card->evolves_from) {
        $evoStages->push(['name' => $card->evolves_from, 'card' => null, 'current' => false]);
    }
    $evoStages->push(['name' => $card->name, 'card' => $card, 'current' => true]);
    foreach ($evolvesTo as $e) {
        $evoStages->push(['name' => $e['name'], 'card' => $e['card'], 'current' => false]);
    }
@endphp

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

                {{-- Market value panel — this is a price tracker, the
                     card itself is not for sale here. --}}
                <div class="mt-8 grid gap-4 rounded-3xl border border-ink-200 bg-white p-6 md:grid-cols-2">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500">Tracked market value</p>
                        @if($card->has_market_price)
                            <p class="mt-1 font-display text-4xl font-black text-ink-900">
                                {{ $rp($card->market_price) }}
                            </p>
                        @else
                            <p class="mt-1 font-display text-2xl font-bold text-ink-500 italic">Price unavailable</p>
                        @endif
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            @if($priceStats['change30d'] !== null)
                                <span class="inline-flex items-center gap-1 rounded-full bg-ink-50 px-2.5 py-1 text-xs font-bold {{ $changeClass($priceStats['change30d']) }}">
                                    {{ $changeText($priceStats['change30d']) }} <span class="font-normal text-ink-400">30d</span>
                                </span>
                            @endif
                            <span class="text-xs text-ink-500">
                                @if($card->has_market_price)
                                    Latest market value from TCGplayer data.
                                @else
                                    No market data returned for this card by TCGplayer.
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="md:border-l md:border-ink-200 md:pl-6">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500">Card details</p>
                        <p class="mt-1 text-sm text-ink-700">
                            {{ $card->set_name }}
                        </p>
                        <p class="mt-1 text-xs text-ink-500">
                            {{ $card->rarity ?? 'Common' }} · #{{ $card->number }}
                            @if($setTotal > 1)
                                · {{ $setPosition }} of {{ $setTotal }} in set
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Action buttons — add the card to your chase list to
                     keep tracking its market value. --}}
                <div class="mt-6 flex flex-wrap items-center gap-3">
                    @auth
                        <form method="POST" action="{{ route('wishlist.toggle', $card) }}">
                            @csrf
                            @php
                                $isChased = auth()->check() && auth()->user()->wishlistedCards->contains($card->id);
                            @endphp
                            <x-prism-button
                                type="submit"
                                variant="{{ $isChased ? 'solid' : 'ghost' }}"
                                size="lg"
                                class="{{ $isChased ? 'bg-rose-500 text-white border-rose-500 hover:bg-rose-600' : '' }}"
                            >
                                <svg class="h-5 w-5"
                                     viewBox="0 0 24 24"
                                     fill="{{ $isChased ? 'currentColor' : 'none' }}"
                                     stroke="currentColor"
                                     stroke-width="1.8">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5A4.69 4.69 0 0 0 12 6.073a4.69 4.69 0 0 0-4.313-2.323C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
                                </svg>
                                {{ $isChased ? 'Chasing this card' : 'Add to chase list' }}
                            </x-prism-button>
                        </form>
                    @else
                        <x-prism-button :href="route('login')" variant="ghost" size="lg">Log in to chase this card</x-prism-button>
                    @endauth

                    @if($chaserCount > 0)
                        <span class="text-xs text-ink-500">
                            {{ $chaserCount }} {{ Str::plural('trainer', $chaserCount) }} chasing this card
                        </span>
                    @endif
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
     PRICE HISTORY — the core of the tracker
     ===================================================== --}}
<section class="mx-auto max-w-[1400px] px-4 pb-12 md:px-8">
    <x-section-heading
        eyebrow="Price tracker"
        title="Market value history" />

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        {{-- Chart --}}
        <div class="rounded-3xl border border-ink-200 bg-white p-6 lg:col-span-2">
            <x-price-chart :history="$history" />
        </div>

        {{-- Stat tiles --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="rounded-2xl border border-ink-200 bg-white p-5">
                <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500">7-day change</p>
                <p class="mt-1 font-display text-2xl font-black {{ $changeClass($priceStats['change7d']) }}">
                    {{ $changeText($priceStats['change7d']) }}
                </p>
            </div>
            <div class="rounded-2xl border border-ink-200 bg-white p-5">
                <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500">30-day change</p>
                <p class="mt-1 font-display text-2xl font-black {{ $changeClass($priceStats['change30d']) }}">
                    {{ $changeText($priceStats['change30d']) }}
                </p>
            </div>
            <div class="rounded-2xl border border-ink-200 bg-white p-5">
                <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500">All-time high</p>
                <p class="mt-1 font-display text-xl font-black text-ink-900">{{ $rp($priceStats['high']) }}</p>
            </div>
            <div class="rounded-2xl border border-ink-200 bg-white p-5">
                <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500">All-time low</p>
                <p class="mt-1 font-display text-xl font-black text-ink-900">{{ $rp($priceStats['low']) }}</p>
            </div>
        </div>
    </div>
</section>

{{-- =====================================================
     EVOLUTION LINE
     ===================================================== --}}
@if($card->evolves_from || $evolvesTo->isNotEmpty())
<section class="mx-auto max-w-[1400px] px-4 pb-12 md:px-8">
    <h2 class="mb-4 font-display text-xl font-black text-ink-900">Evolution line</h2>
    <div class="flex flex-wrap items-center gap-3 rounded-3xl border border-ink-200 bg-white p-6">
        @foreach($evoStages as $i => $stage)
            @if($i > 0)
                <svg class="h-5 w-5 shrink-0 text-ink-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12l-7.5 7.5M3 12h17" />
                </svg>
            @endif

            @php $stageCard = $stage['card']; @endphp
            <a @if($stageCard && ! $stage['current']) href="{{ route('cards.show', $stageCard) }}" @endif
               class="flex items-center gap-3 rounded-2xl border px-3 py-2 transition
                      {{ $stage['current']
                            ? 'border-prism-violet bg-prism-violet/5 ring-1 ring-prism-violet/30'
                            : ($stageCard ? 'border-ink-200 hover:border-ink-400 hover:bg-ink-50' : 'border-dashed border-ink-200') }}">
                @if($stageCard && $stageCard->image_small)
                    <img src="{{ $stageCard->image_small }}" alt="{{ $stage['name'] }}"
                         class="h-16 w-auto rounded shadow-sm" loading="lazy">
                @else
                    <div class="flex h-16 w-12 items-center justify-center rounded bg-ink-100 text-ink-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                        </svg>
                    </div>
                @endif
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-ink-400">
                        {{ $stage['current'] ? 'This card' : ($i === 0 && $card->evolves_from ? 'Evolves from' : 'Evolves to') }}
                    </p>
                    <p class="font-display text-sm font-black text-ink-900">{{ $stage['name'] }}</p>
                </div>
            </a>
        @endforeach
    </div>
    @if($evolvesTo->contains(fn ($e) => $e['card'] === null) || ($card->evolves_from && ! $evolvesFrom))
        <p class="mt-2 text-xs text-ink-400">Greyed-out stages aren’t in the tracked Standard catalogue.</p>
    @endif
</section>
@endif

{{-- =====================================================
     ABILITIES / ATTACKS / WEAKNESSES / RESISTANCES
     ===================================================== --}}
@if(!empty($card->abilities) || !empty($card->attacks) || !empty($card->weaknesses) || !empty($card->retreat_cost))
<section class="mx-auto max-w-[1400px] px-4 pb-16 md:px-8">
    <div class="grid gap-6 lg:grid-cols-3">
        {{-- ABILITIES + ATTACKS --}}
        <div class="space-y-8 lg:col-span-2">
            {{-- ABILITIES --}}
            @if(!empty($card->abilities))
                <div>
                    <h2 class="mb-3 font-display text-xl font-black text-ink-900">Abilities</h2>
                    <div class="space-y-4">
                        @foreach($card->abilities as $ability)
                            <article class="overflow-hidden rounded-2xl border border-prism-violet/30 bg-white">
                                <div class="flex items-center gap-2 border-b border-prism-violet/15 bg-prism-violet/5 px-5 py-3">
                                    <span class="rounded-full bg-prism-violet px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-white">
                                        {{ $ability['type'] ?? 'Ability' }}
                                    </span>
                                    <h3 class="font-display text-base font-black text-ink-900">{{ $ability['name'] ?? 'Ability' }}</h3>
                                </div>
                                @if(!empty($ability['text']))
                                    <p class="px-5 py-3 text-sm leading-relaxed text-ink-700">{{ $ability['text'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ATTACKS --}}
            @if(!empty($card->attacks))
                <div>
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
        </div>

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
     SET & POKÉDEX CONTEXT + COMMUNITY ACTIVITY
     ===================================================== --}}
<section class="mx-auto max-w-[1400px] px-4 pb-16 md:px-8">
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- SET & POKÉDEX --}}
        <div class="rounded-3xl border border-ink-200 bg-white p-6">
            <h2 class="font-display text-xl font-black text-ink-900">Set &amp; Pokédex</h2>
            <dl class="mt-4 divide-y divide-ink-100 text-sm">
                <div class="flex justify-between gap-4 py-2.5">
                    <dt class="text-ink-500">Set</dt>
                    <dd class="text-right font-medium text-ink-900">{{ $card->set_name }}
                        <span class="font-mono text-xs text-ink-400">({{ $card->set_id }})</span>
                    </dd>
                </div>
                <div class="flex justify-between gap-4 py-2.5">
                    <dt class="text-ink-500">Series</dt>
                    <dd class="text-right font-medium text-ink-900">{{ $card->set_series }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-2.5">
                    <dt class="text-ink-500">Card number</dt>
                    <dd class="text-right font-medium text-ink-900">
                        #{{ $card->number }}
                        @if($setTotal > 1)
                            <span class="text-xs text-ink-400">· {{ $setPosition }} of {{ $setTotal }}</span>
                        @endif
                    </dd>
                </div>
                @if(!empty($card->national_pokedex_numbers))
                    <div class="flex justify-between gap-4 py-2.5">
                        <dt class="text-ink-500">National Pokédex</dt>
                        <dd class="text-right font-mono font-medium text-ink-900">
                            {{ collect($card->national_pokedex_numbers)->map(fn ($n) => '#' . str_pad((string) $n, 4, '0', STR_PAD_LEFT))->implode(', ') }}
                        </dd>
                    </div>
                @endif
                <div class="flex justify-between gap-4 py-2.5">
                    <dt class="text-ink-500">Rarity</dt>
                    <dd class="text-right font-medium text-ink-900">{{ $card->rarity ?? 'Common' }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-2.5">
                    <dt class="text-ink-500">Regulation</dt>
                    <dd class="max-w-[60%] text-right">
                        <span class="font-medium text-ink-900">{{ $card->regulation_mark ?? '—' }}</span>
                        <span class="block text-xs text-ink-400">{{ $regNote }}</span>
                    </dd>
                </div>
                @if($card->artist)
                    <div class="flex justify-between gap-4 py-2.5">
                        <dt class="text-ink-500">Illustrator</dt>
                        <dd class="text-right font-medium text-ink-900">{{ $card->artist }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- COMMUNITY ACTIVITY --}}
        <div class="rounded-3xl border border-ink-200 bg-white p-6">
            <h2 class="font-display text-xl font-black text-ink-900">Community activity</h2>

            <div class="mt-4 flex items-center gap-3 rounded-2xl bg-ink-50 px-4 py-3">
                <span class="font-display text-2xl font-black text-ink-900">{{ $chaserCount }}</span>
                <span class="text-sm text-ink-600">{{ Str::plural('trainer', $chaserCount) }} chasing this card</span>
            </div>

            {{-- Active auctions featuring this card --}}
            <div class="mt-5">
                <h3 class="text-[10px] font-bold uppercase tracking-widest text-ink-500">Active auctions</h3>
                @if($activeAuctions->isNotEmpty())
                    <ul class="mt-2 space-y-2">
                        @foreach($activeAuctions as $auction)
                            <li>
                                <a href="{{ route('auctions.show', $auction) }}"
                                   class="flex items-center justify-between gap-3 rounded-xl border border-ink-200 px-4 py-2.5 transition hover:border-ink-400 hover:bg-ink-50">
                                    <span class="flex items-center gap-2">
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest
                                            {{ $auction->is_live ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $auction->is_live ? 'Live' : 'Scheduled' }}
                                        </span>
                                        <span class="text-sm text-ink-600">
                                            {{ $auction->is_live
                                                ? 'Ends ' . $auction->ends_at->diffForHumans()
                                                : 'Starts ' . $auction->starts_at->diffForHumans() }}
                                        </span>
                                    </span>
                                    <span class="font-display text-sm font-black text-ink-900">
                                        {{ $rp($auction->current_bid > 0 ? $auction->current_bid : $auction->starting_bid) }}
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-2 text-sm text-ink-400">No active auctions for this card right now.</p>
                @endif
            </div>

            {{-- Forum threads mentioning this card --}}
            <div class="mt-5">
                <h3 class="text-[10px] font-bold uppercase tracking-widest text-ink-500">Forum mentions</h3>
                @if($forumThreads->isNotEmpty())
                    <ul class="mt-2 space-y-2">
                        @foreach($forumThreads as $thread)
                            <li>
                                <a href="{{ route('forums.thread', $thread) }}"
                                   class="flex items-center justify-between gap-3 rounded-xl border border-ink-200 px-4 py-2.5 transition hover:border-ink-400 hover:bg-ink-50">
                                    <span class="truncate text-sm font-medium text-ink-900">{{ $thread->title }}</span>
                                    <span class="shrink-0 text-xs text-ink-400">
                                        {{ $thread->posts_count }} {{ Str::plural('reply', $thread->posts_count) }}
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-2 text-sm text-ink-400">No forum threads mention this card yet.</p>
                @endif
            </div>
        </div>
    </div>
</section>

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
