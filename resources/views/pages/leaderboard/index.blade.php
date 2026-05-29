<x-app-layout
    title="Trainer Leaderboard"
    description="See the top PokeTrade trainers ranked by collection, points, and activity. Climb the ranks and earn your spot.">


<section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-ink-900 via-prism-violet/20 to-ink-900"></div>
    <div class="absolute inset-0 -z-10 halftone opacity-10"></div>

    <div class="mx-auto max-w-[1200px] px-4 py-20 md:px-8">
        <div class="text-center">
            <span class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-white backdrop-blur">
                Trainer rankings
            </span>
            <h1 class="mt-4 font-display text-5xl font-black leading-[0.95] text-white md:text-7xl">
                The <span class="prism-text">leaderboard</span>.
            </h1>
            <p class="mt-4 mx-auto max-w-2xl text-white/70">
                Top collectors by deck depth, fiercest bidders, and the trainers with the deepest point banks.
            </p>
        </div>
    </div>
</section>

<section class="mx-auto max-w-[1200px] px-4 py-16 md:px-8">
    <div class="grid gap-8 md:grid-cols-3">

        @php
            $boards = [
                ['title' => 'Top collectors', 'eyebrow' => 'Deepest vaults', 'users' => $collectors,    'metricLabel' => 'cards', 'metricKey' => 'collection_count'],
                ['title' => 'Bid kings',      'eyebrow' => 'Auction wins',   'users' => $bidders,       'metricLabel' => 'wins',  'metricKey' => 'wins_count'],
                ['title' => 'Point hoarders', 'eyebrow' => 'Loyalty points', 'users' => $pointHoarders, 'metricLabel' => 'pts',   'metricKey' => 'points'],
            ];
        @endphp

        @foreach($boards as $board)
            <div class="rounded-3xl border border-ink-200 bg-white p-6">
                <div class="mb-5">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500">{{ $board['eyebrow'] }}</p>
                    <h2 class="mt-1 font-display text-2xl font-black text-ink-900">{{ $board['title'] }}</h2>
                </div>

                @if($board['users']->isEmpty())
                    <p class="rounded-2xl bg-ink-50 px-4 py-6 text-center text-sm text-ink-500">
                        Nobody on the board yet.
                    </p>
                @else
                    <ol class="space-y-2">
                        @foreach($board['users'] as $i => $u)
                            @php
                                $rank = $i + 1;
                                $rankColor = match($rank) {
                                    1 => 'bg-gradient-to-br from-prism-gold to-prism-pink text-ink-900',
                                    2 => 'bg-ink-200 text-ink-900',
                                    3 => 'bg-amber-200 text-amber-900',
                                    default => 'bg-ink-100 text-ink-700',
                                };
                            @endphp
                            <li class="flex items-center gap-3 rounded-2xl border border-ink-200 px-3 py-2 transition hover:border-prism-violet">
                                <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-black {{ $rankColor }}">
                                    {{ $rank }}
                                </span>
                                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full prism-bg text-xs font-bold text-white">
                                    {{ Str::upper(Str::substr($u->name, 0, 1)) }}
                                </span>
                                <a href="{{ route('profiles.show', $u) }}" class="flex-1 truncate text-sm font-semibold text-ink-900 hover:text-prism-violet">
                                    {{ $u->name }}
                                </a>
                                <span class="font-mono text-sm font-bold text-ink-900">
                                    {{ number_format($u->{$board['metricKey']}) }}
                                    <span class="text-[10px] font-normal text-ink-500">{{ $board['metricLabel'] }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        @endforeach

    </div>
</section>

</x-app-layout>
