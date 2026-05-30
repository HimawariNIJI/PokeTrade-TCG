<x-app-layout>

@php
    // accent ∈ violet|pink|mint|sky|gold — full literal classes for the JIT.
    $accentBg = [
        'violet' => 'bg-prism-violet', 'pink' => 'bg-prism-pink', 'mint' => 'bg-prism-mint',
        'sky'    => 'bg-prism-sky',    'gold' => 'bg-prism-gold',
    ][$category->accent] ?? 'bg-prism-violet';
@endphp

{{-- ── Category header ──────────────────────────────────────────── --}}
<section class="relative isolate overflow-hidden bg-ink-900">
    <img src="{{ asset('images/gacha-banner.png') }}" alt=""
         class="pointer-events-none absolute inset-0 -z-10 h-full w-full object-cover object-right select-none"
         loading="eager" fetchpriority="high">
    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-r from-ink-900/90 via-ink-900/55 to-transparent"></div>
    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-b from-ink-900/70 via-transparent to-ink-900/40 md:hidden"></div>
    <div class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-24 bg-gradient-to-t from-ink-900 to-transparent"></div>
    <div class="pointer-events-none absolute inset-0 -z-10 halftone opacity-10"></div>
    <span class="pointer-events-none absolute inset-x-0 top-0 z-10 h-1 {{ $accentBg }}"></span>

    <div class="mx-auto max-w-[1400px] px-4 pb-14 pt-16 md:px-8 md:pt-20">
        <nav class="mb-4 flex items-center gap-2 text-xs font-semibold text-white/60">
            <a href="{{ route('forums.index') }}" class="transition hover:text-white">Forums</a>
            <span>/</span>
            <span class="text-white">{{ $category->name }}</span>
        </nav>
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <h1 class="font-display text-4xl font-black tracking-tight text-white md:text-5xl">
                    {{ $category->name }}
                </h1>
                @if($category->description)
                    <p class="mt-3 max-w-2xl text-white/70">{{ $category->description }}</p>
                @endif
            </div>
            @auth
                <x-prism-button :href="route('forums.create')">
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

    {{-- ── Thread list ──────────────────────────────────────────── --}}
    @if($threads->isNotEmpty())
        <div class="divide-y divide-ink-100 overflow-hidden rounded-3xl border border-ink-200 bg-white">
            @foreach($threads as $thread)
                <a href="{{ route('forums.thread', $thread) }}"
                   class="group flex items-start gap-4 px-5 py-5 transition hover:bg-ink-50">
                    {{-- Author avatar --}}
                    @if($thread->author?->avatar)
                        <img src="{{ $thread->author->avatar }}" alt="{{ $thread->author->name }}"
                             class="h-11 w-11 shrink-0 rounded-full object-cover">
                    @else
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full prism-bg text-sm font-bold text-white">
                            {{ Str::upper(Str::substr($thread->author?->name ?? '?', 0, 1)) }}
                        </span>
                    @endif

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            @if($thread->pinned)
                                <span class="inline-flex items-center gap-1 rounded-full bg-prism-gold/20 px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest text-ink-700">
                                    📌 Pinned
                                </span>
                            @endif
                            <h3 class="line-clamp-1 font-display text-base font-black text-ink-900 transition group-hover:text-prism-violet">
                                {{ $thread->title }}
                            </h3>
                        </div>
                        <p class="mt-1 text-xs text-ink-500">
                            by <span class="font-semibold text-ink-700">{{ $thread->author?->name ?? 'Unknown' }}</span>
                            · {{ $thread->last_posted_at?->diffForHumans() ?? $thread->created_at->diffForHumans() }}
                        </p>
                    </div>

                    {{-- Stats --}}
                    <div class="hidden shrink-0 items-center gap-4 text-right sm:flex">
                        <div>
                            <p class="font-mono text-sm font-bold text-ink-900">{{ $thread->posts_count }}</p>
                            <p class="text-[10px] uppercase tracking-widest text-ink-500">{{ Str::plural('reply', $thread->posts_count) }}</p>
                        </div>
                        <div>
                            <p class="font-mono text-sm font-bold text-ink-900">{{ $thread->views }}</p>
                            <p class="text-[10px] uppercase tracking-widest text-ink-500">{{ Str::plural('view', $thread->views) }}</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">{{ $threads->links() }}</div>
    @else
        <x-empty-state
            icon="✦"
            title="No threads in {{ $category->name }} yet"
            message="This board is wide open. Be the first to post.">
            @auth
                <x-prism-button :href="route('forums.create')" size="sm">Start a thread</x-prism-button>
            @else
                <x-prism-button :href="route('login')" size="sm" variant="ghost">Log in to post</x-prism-button>
            @endauth
        </x-empty-state>
    @endif
</div>

</x-app-layout>
