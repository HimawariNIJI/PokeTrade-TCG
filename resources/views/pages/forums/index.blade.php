<x-app-layout
    title="Community Forums"
    description="Talk Pokemon TCG with the PokeTrade community: deck talk, pulls, trades, market chat, and Prismatic Evolutions news.">


@php
    // Map a category's prism accent token to FULL utility class strings.
    // accent ∈ violet|pink|mint|sky|gold — defaults to violet if unset.
    // Full literal classes (no interpolation) so Tailwind's JIT scanner
    // picks every variant up at build time.
    $accentMap = [
        'violet' => ['border' => 'hover:border-prism-violet', 'bg' => 'bg-prism-violet'],
        'pink'   => ['border' => 'hover:border-prism-pink',   'bg' => 'bg-prism-pink'],
        'mint'   => ['border' => 'hover:border-prism-mint',   'bg' => 'bg-prism-mint'],
        'sky'    => ['border' => 'hover:border-prism-sky',    'bg' => 'bg-prism-sky'],
        'gold'   => ['border' => 'hover:border-prism-gold',   'bg' => 'bg-prism-gold'],
    ];
@endphp

{{-- ── Hero ─────────────────────────────────────────────────────── --}}
<x-page-hero
    compact
    mon="jolteon"
    eyebrow="Community"
    title='The <span class="prism-text">Forums</span>.'
    subtitle="Trade talk, pull brags, deck tech, and grading questions. This is where the collector community gathers.">
    @auth
        <x-slot:actions>
            <x-prism-button :href="route('forums.create')">
                Start a thread
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"/>
                </svg>
            </x-prism-button>
        </x-slot:actions>
    @endauth
</x-page-hero>

<div class="mx-auto max-w-[1400px] px-4 py-16 md:px-8 md:py-20">
    <div class="grid gap-12 lg:grid-cols-12">

        {{-- ── Main column ─────────────────────────────────────── --}}
        <div class="lg:col-span-8">

            {{-- Category board --}}
            <x-section-heading
                eyebrow="Boards"
                title="Browse by <span class='prism-text'>topic</span>" />

            @if($categories->isNotEmpty())
                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    @foreach($categories as $category)
                        @php $accent = $accentMap[$category->accent] ?? $accentMap['violet']; @endphp
                        <a href="{{ route('forums.category', $category) }}"
                           class="group relative block overflow-hidden rounded-3xl border border-ink-200 bg-white p-5 transition hover:-translate-y-1 {{ $accent['border'] }} hover:shadow-xl duration-300">
                            <span class="absolute inset-x-0 top-0 h-1 {{ $accent['bg'] }} opacity-70"></span>
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="font-display text-lg font-black text-ink-900">{{ $category->name }}</h3>
                                <span class="shrink-0 rounded-full bg-ink-100 px-2.5 py-1 font-mono text-[11px] font-bold text-ink-700">
                                    {{ $category->threads_count }} {{ Str::plural('thread', $category->threads_count) }}
                                </span>
                            </div>
                            @if($category->description)
                                <p class="mt-2 line-clamp-2 text-sm text-ink-500">{{ $category->description }}</p>
                            @endif
                            @php $latest = $category->threads->first(); @endphp
                            <div class="mt-4 flex items-center gap-2 text-xs text-ink-500">
                                @if($latest)
                                    <span class="h-1.5 w-1.5 rounded-full {{ $accent['bg'] }}"></span>
                                    <span class="line-clamp-1">
                                        Latest: <span class="font-semibold text-ink-700">{{ $latest->title }}</span>
                                    </span>
                                @else
                                    <span class="italic">No threads yet — be the first.</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="mt-8">
                    <x-empty-state
                        icon="◇"
                        title="No boards yet"
                        message="Forum categories haven't been set up. Check back soon." />
                </div>
            @endif

            {{-- Recent discussions --}}
            <div class="mt-14">
                <x-section-heading
                    eyebrow="Activity"
                    title="Recent <span class='prism-text'>discussions</span>" />

                @if($recentThreads->isNotEmpty())
                    <div class="mt-6 divide-y divide-ink-100 overflow-hidden rounded-3xl border border-ink-200 bg-white">
                        @foreach($recentThreads as $thread)
                            <a href="{{ route('forums.thread', $thread) }}"
                               class="group flex items-center gap-4 px-5 py-4 transition hover:bg-ink-50">
                                {{-- Author avatar --}}
                                @if($thread->author?->avatar)
                                    <img src="{{ $thread->author->avatar }}" alt="{{ $thread->author->name }}"
                                         class="h-10 w-10 shrink-0 rounded-full object-cover">
                                @else
                                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full prism-bg text-sm font-bold text-white">
                                        {{ Str::upper(Str::substr($thread->author?->name ?? '?', 0, 1)) }}
                                    </span>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="line-clamp-1 font-display text-sm font-bold text-ink-900 transition group-hover:text-prism-violet">
                                        {{ $thread->title }}
                                    </p>
                                    <p class="mt-0.5 line-clamp-1 text-xs text-ink-500">
                                        {{ $thread->author?->name ?? 'Unknown' }}
                                        @if($thread->category)
                                            · in <span class="font-semibold">{{ $thread->category->name }}</span>
                                        @endif
                                        · {{ $thread->last_posted_at?->diffForHumans() ?? $thread->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <span class="hidden shrink-0 rounded-full bg-ink-100 px-2.5 py-1 font-mono text-[11px] font-bold text-ink-700 sm:inline">
                                    {{ $thread->posts_count }} {{ Str::plural('reply', $thread->posts_count) }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="mt-6">
                        <x-empty-state
                            icon="✦"
                            title="Nothing's been posted yet"
                            message="The forums are quiet. Start the first conversation." />
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Community chat panel ────────────────────────────── --}}
        {{--
            Alpine-driven demo chat. Messages prepend client-side only —
            nothing is persisted or broadcast. See ForumController@index.
        --}}
        <aside class="lg:col-span-4">
            <div x-data="{
                    draft: '',
                    messages: @js(collect($chatMessages)->map(fn ($m) => [
                        'name' => $m['name'],
                        'body' => $m['body'],
                        'ago'  => $m['minutes_ago'] . 'm ago',
                    ])->values()),
                    send() {
                        const text = this.draft.trim();
                        if (! text) return;
                        this.messages.unshift({ name: 'You', body: text, ago: 'just now' });
                        this.draft = '';
                    }
                 }"
                 class="sticky top-24 overflow-hidden rounded-3xl border border-ink-200 bg-white shadow-sm">

                {{-- Panel header --}}
                <div class="relative border-b border-ink-100 px-5 py-4">
                    <span class="absolute inset-x-0 top-0 h-1 prism-bg"></span>
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-display text-base font-black text-ink-900">Community chat</h3>
                            <p class="text-xs text-ink-500">Live banter from the floor</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-prism-mint/15 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-ink-700">
                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-prism-mint"></span>
                            Live
                        </span>
                    </div>
                </div>

                {{-- Message stream --}}
                <div class="flex max-h-[420px] flex-col gap-3 overflow-y-auto px-5 py-4">
                    <template x-for="(msg, i) in messages" :key="i">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full prism-bg text-[11px] font-bold text-white"
                                  x-text="msg.name.charAt(0).toUpperCase()"></span>
                            <div class="min-w-0 flex-1">
                                <p class="flex items-baseline gap-2">
                                    <span class="font-display text-xs font-bold text-ink-900" x-text="msg.name"></span>
                                    <span class="font-mono text-[10px] text-ink-500" x-text="msg.ago"></span>
                                </p>
                                <p class="mt-0.5 text-sm text-ink-700" x-text="msg.body"></p>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Composer --}}
                <div class="border-t border-ink-100 p-3">
                    <form @submit.prevent="send()" class="flex items-center gap-2">
                        <input type="text" x-model="draft"
                               placeholder="Say something nice…"
                               class="min-w-0 flex-1 rounded-full border-ink-200 bg-ink-50 px-4 py-2 text-sm placeholder:text-ink-500 focus:border-prism-violet focus:ring-prism-violet">
                        <button type="submit"
                                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full prism-bg text-white transition hover:scale-105 active:scale-95">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.27 3.13a.5.5 0 0 1 .67-.6l16.5 8.5a.5.5 0 0 1 0 .94l-16.5 8.5a.5.5 0 0 1-.67-.6L6 12Zm0 0h8"/>
                            </svg>
                        </button>
                    </form>
                    <p class="mt-2 px-2 text-[11px] text-ink-500">Demo chat — messages aren't saved yet.</p>
                </div>
            </div>
        </aside>
    </div>
</div>

</x-app-layout>
