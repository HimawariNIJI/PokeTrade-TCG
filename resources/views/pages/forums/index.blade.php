<x-app-layout
    title="Community Forums"
    description="Talk Pokemon TCG with the PokeTrade community: deck talk, pulls, trades, market chat, and Prismatic Evolutions news.">


@php
    // Map a category's prism accent token to FULL utility class strings.
    // accent ∈ violet|pink|mint|sky|gold — defaults to violet if unset.
    $accentMap = [
        'violet' => ['border' => 'hover:border-prism-violet', 'bg' => 'bg-prism-violet'],
        'pink'   => ['border' => 'hover:border-prism-pink',   'bg' => 'bg-prism-pink'],
        'mint'   => ['border' => 'hover:border-prism-mint',   'bg' => 'bg-prism-mint'],
        'sky'    => ['border' => 'hover:border-prism-sky',    'bg' => 'bg-prism-sky'],
        'gold'   => ['border' => 'hover:border-prism-gold',   'bg' => 'bg-prism-gold'],
    ];

    // Initial shoutbox payload, oldest-first for chat-style display.
    $initialShouts = $shouts->map(fn ($m) => [
        'id'   => $m->id,
        'name' => $m->user?->name ?? 'Trainer',
        'body' => $m->body,
        'ago'  => $m->created_at->diffForHumans(null, true) . ' ago',
    ])->reverse()->values();
@endphp

{{-- ── Hero ─────────────────────────────────────────────────────── --}}
<section class="relative isolate overflow-hidden bg-ink-900">
    <img src="{{ asset('images/gacha-banner.png') }}" alt=""
         class="pointer-events-none absolute inset-0 -z-10 h-full w-full object-cover object-right select-none"
         loading="eager" fetchpriority="high">
    {{-- Left-fade overlay so headline + search keep AA contrast over the bright burst on the right --}}
    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-r from-ink-900/90 via-ink-900/55 to-transparent"></div>
    {{-- On mobile the image stacks under the copy, so add a vertical wash for text contrast --}}
    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-b from-ink-900/70 via-transparent to-ink-900/40 md:hidden"></div>
    {{-- Bottom fade keeps the seam against the next section soft --}}
    <div class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-24 bg-gradient-to-t from-ink-900 to-transparent"></div>
    <div class="pointer-events-none absolute inset-0 -z-10 halftone opacity-10"></div>
    <x-prism-aurora />
    <div class="pointer-events-none absolute inset-x-0 top-0 z-10 h-px prism-bg"></div>

    <div class="mx-auto max-w-[1400px] px-4 pb-14 pt-16 md:min-h-[420px] md:px-8 md:pt-20">
        <span class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-white backdrop-blur">
            <span class="h-2 w-2 rounded-full bg-prism-mint"></span>
            Community
        </span>
        <h1 class="mt-4 font-display text-5xl font-black tracking-tight text-white md:text-6xl">
            The <span class="prism-text">Forums</span>.
        </h1>
        <p class="mt-3 max-w-2xl text-white/70">
            Trade talk, pull brags, deck tech, and grading questions. This is where the collector community gathers.
        </p>

        <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:items-center">
            {{-- Search --}}
            <form method="GET" action="{{ route('forums.index') }}" class="relative w-full sm:max-w-md">
                <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-white/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="search" name="q" value="{{ $query }}" placeholder="Search threads…"
                       class="w-full rounded-full border-white/20 bg-white/10 py-2.5 pl-11 pr-4 text-sm text-white placeholder:text-white/50 backdrop-blur focus:border-prism-mint focus:ring-prism-mint">
            </form>
            @auth
                <x-prism-button :href="route('forums.create')" size="md">
                    Start a thread
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"/>
                    </svg>
                </x-prism-button>
            @endauth
        </div>
    </div>
</section>

<div class="mx-auto max-w-[1400px] px-4 py-16 md:px-8 md:py-20">
    <div class="grid gap-12 lg:grid-cols-12">

        {{-- ── Main column ─────────────────────────────────────── --}}
        <div class="lg:col-span-8">

            {{-- Search results --}}
            @if($query !== '')
                <div class="mb-12">
                    <div class="flex items-end justify-between gap-4">
                        <x-section-heading
                            eyebrow="Search"
                            title='Results for <span class="prism-text">{{ e($query) }}</span>' />
                        <a href="{{ route('forums.index') }}" class="shrink-0 text-sm font-bold text-ink-500 hover:text-ink-900">Clear</a>
                    </div>

                    @if($results->isNotEmpty())
                        <div class="mt-6 divide-y divide-ink-100 overflow-hidden rounded-3xl border border-ink-200 bg-white">
                            @foreach($results as $thread)
                                <a href="{{ route('forums.thread', $thread) }}" class="group flex items-center gap-4 px-5 py-4 transition hover:bg-ink-50">
                                    <div class="min-w-0 flex-1">
                                        <p class="line-clamp-1 font-display text-sm font-bold text-ink-900 transition group-hover:text-prism-violet">{{ $thread->title }}</p>
                                        <p class="mt-0.5 line-clamp-1 text-xs text-ink-500">
                                            {{ $thread->author?->name ?? 'Unknown' }}
                                            @if($thread->category) · in <span class="font-semibold">{{ $thread->category->name }}</span> @endif
                                            · {{ $thread->last_posted_at?->diffForHumans() ?? $thread->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <span class="hidden shrink-0 rounded-full bg-ink-100 px-2.5 py-1 font-mono text-[11px] font-bold text-ink-700 sm:inline">
                                        {{ $thread->posts_count }} {{ Str::plural('reply', $thread->posts_count) }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                        <div class="mt-6">{{ $results->links() }}</div>
                    @else
                        <div class="mt-6">
                            <x-empty-state icon="✦" title="No threads found"
                                message="Nothing matched “{{ $query }}”. Try a different search." />
                        </div>
                    @endif
                </div>
            @endif

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
                                    <span class="italic">No threads yet. Be the first.</span>
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

        {{-- ── Community shoutbox (persisted, polled) ──────────── --}}
        <aside class="lg:col-span-4">
            <div x-data="shoutbox(@js($initialShouts), @js(auth()->check()), @js(route('shoutbox.index')), @js(route('shoutbox.store')))"
                 class="sticky top-24 overflow-hidden rounded-3xl border border-ink-200 bg-white shadow-sm">

                <div class="relative border-b border-ink-100 px-5 py-4">
                    <span class="absolute inset-x-0 top-0 h-1 prism-bg"></span>
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-display text-base font-black text-ink-900">Community shoutbox</h3>
                            <p class="text-xs text-ink-500">Quick banter from the floor</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-prism-mint/15 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-ink-700">
                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-prism-mint"></span>
                            Live
                        </span>
                    </div>
                </div>

                {{-- Message stream (oldest at top, newest at bottom) --}}
                <div class="flex max-h-[420px] flex-col gap-3 overflow-y-auto px-5 py-4" x-ref="stream">
                    <template x-for="msg in messages" :key="msg.id">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full prism-bg text-[11px] font-bold text-white"
                                  x-text="msg.name.charAt(0).toUpperCase()"></span>
                            <div class="min-w-0 flex-1">
                                <p class="flex items-baseline gap-2">
                                    <span class="font-display text-xs font-bold text-ink-900" x-text="msg.name"></span>
                                    <span class="font-mono text-[10px] text-ink-500" x-text="msg.ago"></span>
                                </p>
                                <p class="mt-0.5 break-words text-sm text-ink-700" x-text="msg.body"></p>
                            </div>
                        </div>
                    </template>
                    <p x-show="messages.length === 0" class="py-6 text-center text-sm text-ink-500">No messages yet. Say hi 👋</p>
                </div>

                {{-- Composer --}}
                <div class="border-t border-ink-100 p-3">
                    @auth
                        <form @submit.prevent="send()" class="flex items-center gap-2">
                            <input type="text" x-model="draft" maxlength="280"
                                   placeholder="Say something nice…"
                                   class="min-w-0 flex-1 rounded-full border-ink-200 bg-ink-50 px-4 py-2 text-sm placeholder:text-ink-500 focus:border-prism-violet focus:ring-prism-violet">
                            <button type="submit" :disabled="sending" aria-label="Send message"
                                    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full prism-bg-deep text-white transition hover:scale-105 active:scale-95 disabled:opacity-50">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.27 3.13a.5.5 0 0 1 .67-.6l16.5 8.5a.5.5 0 0 1 0 .94l-16.5 8.5a.5.5 0 0 1-.67-.6L6 12Zm0 0h8"/>
                                </svg>
                            </button>
                        </form>
                    @else
                        <p class="px-2 py-1 text-center text-[13px] text-ink-500">
                            <a href="{{ route('login') }}" class="font-semibold text-prism-violet hover:underline">Log in</a> to join the chat.
                        </p>
                    @endauth
                </div>
            </div>
        </aside>
    </div>
</div>

</x-app-layout>
