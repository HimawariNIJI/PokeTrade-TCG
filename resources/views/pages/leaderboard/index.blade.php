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

<section
    class="mx-auto max-w-[1200px] px-4 py-16 md:px-8"
    x-data="liveLeaderboard({ url: '{{ route('leaderboard.data') }}', boards: {{ Illuminate\Support\Js::from($boards) }} })"
    x-init="start()">

    <div class="mb-6 flex items-center justify-end gap-2 text-[11px] font-bold uppercase tracking-widest text-ink-500">
        <span class="relative flex h-2 w-2">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-poke-red opacity-75"></span>
            <span class="relative inline-flex h-2 w-2 rounded-full bg-poke-red"></span>
        </span>
        Live rankings
    </div>

    <div class="grid gap-8 md:grid-cols-3">

        <template x-for="board in boards" :key="board.key">
            <div class="rounded-3xl border border-ink-200 bg-white p-6">
                <div class="mb-5">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500" x-text="board.eyebrow"></p>
                    <h2 class="mt-1 font-display text-2xl font-black text-ink-900" x-text="board.title"></h2>
                </div>

                <template x-if="board.entries.length === 0">
                    <p class="rounded-2xl bg-ink-50 px-4 py-6 text-center text-sm text-ink-500">
                        Nobody on the board yet.
                    </p>
                </template>

                <template x-if="board.entries.length > 0">
                    <ol class="space-y-2">
                        <template x-for="entry in board.entries" :key="entry.rank">
                            <li>
                                <a
                                    :href="entry.profileUrl"
                                    :aria-label="`View ${entry.name}'s profile`"
                                    class="group flex items-center gap-3 rounded-2xl border border-ink-200 px-3 py-2 transition hover:border-prism-violet hover:bg-prism-violet/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-prism-violet">
                                    <span
                                        class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-black"
                                        :class="rankClass(entry.rank)"
                                        x-text="entry.rank"></span>
                                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full prism-bg text-xs font-bold text-white" x-text="entry.initial"></span>
                                    <span class="flex-1 truncate text-sm font-semibold text-ink-900 group-hover:text-prism-violet" x-text="entry.name"></span>
                                    <span class="font-mono text-sm font-bold text-ink-900">
                                        <span x-text="fmt(entry.metric)"></span>
                                        <span class="text-[10px] font-normal text-ink-500" x-text="board.metricLabel"></span>
                                    </span>
                                </a>
                            </li>
                        </template>
                    </ol>
                </template>
            </div>
        </template>

    </div>
</section>

</x-app-layout>
