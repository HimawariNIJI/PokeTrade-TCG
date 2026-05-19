<x-app-layout>

{{-- ── Thread header ───────────────────────────────────────────── --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-ink-900 via-prism-violet/20 to-ink-900"></div>
    <div class="absolute inset-0 -z-10 halftone opacity-10"></div>
    <div class="absolute inset-x-0 top-0 -z-10 h-px prism-bg"></div>

    <div class="mx-auto max-w-[860px] px-4 pb-12 pt-16 md:px-8 md:pt-20">
        <nav class="mb-4 flex flex-wrap items-center gap-2 text-xs font-semibold text-white/60">
            <a href="{{ route('forums.index') }}" class="transition hover:text-white">Forums</a>
            <span>/</span>
            @if($thread->category)
                <a href="{{ route('forums.category', $thread->category) }}" class="transition hover:text-white">
                    {{ $thread->category->name }}
                </a>
                <span>/</span>
            @endif
            <span class="line-clamp-1 text-white">{{ $thread->title }}</span>
        </nav>

        <div class="flex flex-wrap items-center gap-2">
            @if($thread->pinned)
                <span class="inline-flex items-center gap-1 rounded-full bg-prism-gold/30 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-white">
                    📌 Pinned
                </span>
            @endif
        </div>
        <h1 class="mt-2 font-display text-3xl font-black tracking-tight text-white md:text-4xl">
            {{ $thread->title }}
        </h1>
        <p class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-white/60">
            <span>Started by <span class="font-semibold text-white">{{ $thread->author?->name ?? 'Unknown' }}</span></span>
            <span>·</span>
            <span>{{ $thread->created_at->diffForHumans() }}</span>
            <span>·</span>
            <span class="font-mono text-xs">{{ $thread->views }} {{ Str::plural('view', $thread->views) }}</span>
            <span>·</span>
            <span class="font-mono text-xs">{{ $thread->posts->count() }} {{ Str::plural('reply', $thread->posts->count()) }}</span>
        </p>
    </div>
</section>

<div class="mx-auto max-w-[860px] px-4 py-12 md:px-8">

    {{-- ── Original post ───────────────────────────────────────── --}}
    <article class="relative overflow-hidden rounded-3xl border border-ink-200 bg-white p-6 shadow-sm md:p-8">
        <span class="absolute inset-x-0 top-0 h-1 prism-bg"></span>
        <header class="flex items-center gap-3">
            @if($thread->author?->avatar)
                <img src="{{ $thread->author->avatar }}" alt="{{ $thread->author->name }}"
                     class="h-11 w-11 shrink-0 rounded-full object-cover">
            @else
                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full prism-bg text-sm font-bold text-white">
                    {{ Str::upper(Str::substr($thread->author?->name ?? '?', 0, 1)) }}
                </span>
            @endif
            <div>
                <p class="font-display text-sm font-black text-ink-900">{{ $thread->author?->name ?? 'Unknown' }}</p>
                <p class="font-mono text-[11px] text-ink-500">
                    Original poster · {{ $thread->created_at->diffForHumans() }}
                </p>
            </div>
        </header>
        <div class="mt-5 whitespace-pre-line text-[15px] leading-relaxed text-ink-700">{{ $thread->body }}</div>
    </article>

    {{-- ── Replies ─────────────────────────────────────────────── --}}
    <div class="mt-10">
        <h2 class="font-display text-lg font-black text-ink-900">
            {{ $thread->posts->count() }} {{ Str::plural('Reply', $thread->posts->count()) }}
        </h2>

        @if($thread->posts->isNotEmpty())
            <div class="mt-5 space-y-4">
                @foreach($thread->posts as $post)
                    <article class="rounded-3xl border border-ink-200 bg-white p-5 md:p-6">
                        <header class="flex items-center gap-3">
                            @if($post->author?->avatar)
                                <img src="{{ $post->author->avatar }}" alt="{{ $post->author->name }}"
                                     class="h-9 w-9 shrink-0 rounded-full object-cover">
                            @else
                                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full prism-bg text-xs font-bold text-white">
                                    {{ Str::upper(Str::substr($post->author?->name ?? '?', 0, 1)) }}
                                </span>
                            @endif
                            <div>
                                <p class="font-display text-sm font-bold text-ink-900">{{ $post->author?->name ?? 'Unknown' }}</p>
                                <p class="font-mono text-[11px] text-ink-500">{{ $post->created_at->diffForHumans() }}</p>
                            </div>
                        </header>
                        <div class="mt-4 whitespace-pre-line text-[15px] leading-relaxed text-ink-700">{{ $post->body }}</div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="mt-5">
                <x-empty-state
                    icon="✦"
                    title="No replies yet"
                    message="Be the first to weigh in on this thread." />
            </div>
        @endif
    </div>

    {{-- ── Reply composer ──────────────────────────────────────── --}}
    <div class="mt-10">
        @auth
            <div class="relative overflow-hidden rounded-3xl border border-ink-200 bg-white p-6 shadow-sm md:p-8">
                <span class="absolute inset-x-0 top-0 h-1 prism-bg"></span>
                <h3 class="font-display text-base font-black text-ink-900">Post a reply</h3>

                <form method="POST" action="{{ route('forums.reply', $thread) }}" class="mt-4">
                    @csrf
                    <textarea name="body" rows="5" required
                              placeholder="Share your thoughts…"
                              class="w-full rounded-2xl border-ink-200 bg-ink-50 px-4 py-3 text-sm placeholder:text-ink-500 focus:border-prism-violet focus:ring-prism-violet">{{ old('body') }}</textarea>
                    @error('body')
                        <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                    <div class="mt-4 flex justify-end">
                        <x-prism-button type="submit">
                            Post reply
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/>
                            </svg>
                        </x-prism-button>
                    </div>
                </form>
            </div>
        @else
            <div class="flex flex-col items-center gap-4 rounded-3xl border-2 border-dashed border-ink-200 bg-white px-8 py-12 text-center">
                <p class="font-display text-base font-bold text-ink-900">Want to join the conversation?</p>
                <p class="text-sm text-ink-500">Log in to post a reply to this thread.</p>
                <x-prism-button :href="route('login')" size="sm">Log in to reply</x-prism-button>
            </div>
        @endauth
    </div>
</div>

</x-app-layout>
