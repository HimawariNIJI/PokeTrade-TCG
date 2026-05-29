<x-app-layout :title="$thread->title">

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
            @if($thread->locked)
                <span class="inline-flex items-center gap-1 rounded-full bg-white/15 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-white">
                    🔒 Locked
                </span>
            @endif
        </div>
        <h1 class="mt-2 font-display text-3xl font-black tracking-tight text-white md:text-4xl">
            {{ $thread->title }}
        </h1>
        <p class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-white/60">
            <span>Started by
                @if($thread->author)
                    <a href="{{ route('profiles.show', $thread->author) }}" class="font-semibold text-white hover:underline">{{ $thread->author->name }}</a>
                @else
                    <span class="font-semibold text-white">Unknown</span>
                @endif
            </span>
            <span>·</span>
            <span>{{ $thread->created_at->diffForHumans() }}</span>
            <span>·</span>
            <span class="font-mono text-xs">{{ $thread->views }} {{ Str::plural('view', $thread->views) }}</span>
            <span>·</span>
            <span class="font-mono text-xs">{{ $posts->total() }} {{ Str::plural('reply', $posts->total()) }}</span>
        </p>
    </div>
</section>

<div class="mx-auto max-w-[860px] px-4 py-12 md:px-8">

    {{-- ── Moderation toolbar (author / admin) ─────────────────── --}}
    @auth
        @if(auth()->user()->can('update', $thread) || auth()->user()->can('moderate', $thread))
            <div class="mb-6 flex flex-wrap items-center gap-2 rounded-2xl border border-ink-200 bg-white px-4 py-3">
                <span class="mr-1 text-[11px] font-bold uppercase tracking-widest text-ink-500">Manage</span>

                @can('update', $thread)
                    <a href="{{ route('forums.edit', $thread) }}"
                       class="rounded-full border border-ink-200 px-3 py-1.5 text-xs font-bold text-ink-700 transition hover:border-prism-violet hover:text-prism-violet">
                        Edit
                    </a>
                @endcan

                @can('moderate', $thread)
                    <form method="POST" action="{{ route('forums.pin', $thread) }}">
                        @csrf @method('PATCH')
                        <button class="rounded-full border border-ink-200 px-3 py-1.5 text-xs font-bold text-ink-700 transition hover:border-prism-gold hover:text-ink-900">
                            {{ $thread->pinned ? 'Unpin' : 'Pin' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('forums.lock', $thread) }}">
                        @csrf @method('PATCH')
                        <button class="rounded-full border border-ink-200 px-3 py-1.5 text-xs font-bold text-ink-700 transition hover:border-ink-900 hover:text-ink-900">
                            {{ $thread->locked ? 'Unlock' : 'Lock' }}
                        </button>
                    </form>
                @endcan

                @can('delete', $thread)
                    <form method="POST" action="{{ route('forums.destroy', $thread) }}"
                          onsubmit="return confirm('Delete this entire thread and all its replies?')">
                        @csrf @method('DELETE')
                        <button class="rounded-full border border-rose-200 px-3 py-1.5 text-xs font-bold text-rose-600 transition hover:bg-rose-50">
                            Delete thread
                        </button>
                    </form>
                @endcan
            </div>
        @endif
    @endauth

    {{-- ── Original post ───────────────────────────────────────── --}}
    <article class="relative overflow-hidden rounded-3xl border border-ink-200 bg-white p-6 shadow-sm md:p-8">
        <span class="absolute inset-x-0 top-0 h-1 prism-bg"></span>
        <header class="flex items-center gap-3">
            <a href="{{ $thread->author ? route('profiles.show', $thread->author) : '#' }}" class="shrink-0">
                @if($thread->author?->avatar)
                    <img src="{{ $thread->author->avatar }}" alt="{{ $thread->author->name }}" class="h-11 w-11 rounded-full object-cover">
                @else
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-full prism-bg text-sm font-bold text-white">
                        {{ Str::upper(Str::substr($thread->author?->name ?? '?', 0, 1)) }}
                    </span>
                @endif
            </a>
            <div>
                <a href="{{ $thread->author ? route('profiles.show', $thread->author) : '#' }}"
                   class="font-display text-sm font-black text-ink-900 hover:text-prism-violet">{{ $thread->author?->name ?? 'Unknown' }}</a>
                <p class="font-mono text-[11px] text-ink-500">
                    Original poster · {{ $thread->created_at->diffForHumans() }}
                </p>
            </div>
        </header>
        <div class="mt-5 whitespace-pre-line text-[15px] leading-relaxed text-ink-700">{{ $thread->body }}</div>

        <footer class="mt-5 flex items-center justify-end border-t border-ink-100 pt-3">
            @auth
                @if(auth()->id() !== $thread->user_id)
                    <x-report-button type="thread" :id="$thread->id" />
                @endif
            @endauth
        </footer>
    </article>

    {{-- ── Replies ─────────────────────────────────────────────── --}}
    <div class="mt-10">
        <h2 class="font-display text-lg font-black text-ink-900">
            {{ $posts->total() }} {{ Str::plural('Reply', $posts->total()) }}
        </h2>

        @if($posts->isNotEmpty())
            <div class="mt-5 space-y-4">
                @foreach($posts as $post)
                    <article x-data="{ editing: false }" class="rounded-3xl border border-ink-200 bg-white p-5 md:p-6">
                        <header class="flex items-center gap-3">
                            <a href="{{ $post->author ? route('profiles.show', $post->author) : '#' }}" class="shrink-0">
                                @if($post->author?->avatar)
                                    <img src="{{ $post->author->avatar }}" alt="{{ $post->author->name }}" class="h-9 w-9 rounded-full object-cover">
                                @else
                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full prism-bg text-xs font-bold text-white">
                                        {{ Str::upper(Str::substr($post->author?->name ?? '?', 0, 1)) }}
                                    </span>
                                @endif
                            </a>
                            <div class="min-w-0">
                                <a href="{{ $post->author ? route('profiles.show', $post->author) : '#' }}"
                                   class="font-display text-sm font-bold text-ink-900 hover:text-prism-violet">{{ $post->author?->name ?? 'Unknown' }}</a>
                                <p class="font-mono text-[11px] text-ink-500">{{ $post->created_at->diffForHumans() }}</p>
                            </div>
                        </header>

                        {{-- Read mode --}}
                        <div x-show="!editing" class="mt-4 whitespace-pre-line text-[15px] leading-relaxed text-ink-700">{{ $post->body }}</div>

                        {{-- Edit mode (author / admin) --}}
                        @can('update', $post)
                            <form x-show="editing" x-cloak method="POST" action="{{ route('forums.posts.update', $post) }}" class="mt-4">
                                @csrf @method('PUT')
                                <textarea name="body" rows="4" required
                                          class="w-full rounded-2xl border-ink-200 bg-ink-50 px-4 py-3 text-sm focus:border-prism-violet focus:ring-prism-violet">{{ $post->body }}</textarea>
                                <div class="mt-2 flex justify-end gap-2">
                                    <button type="button" @click="editing = false" class="rounded-full border border-ink-200 px-4 py-1.5 text-xs font-bold text-ink-700 hover:bg-ink-50">Cancel</button>
                                    <button type="submit" class="rounded-full prism-bg-deep px-4 py-1.5 text-xs font-bold text-white">Save</button>
                                </div>
                            </form>
                        @endcan

                        {{-- Actions --}}
                        <footer class="mt-4 flex items-center justify-end gap-3 border-t border-ink-100 pt-3 text-xs">
                            @can('update', $post)
                                <button type="button" @click="editing = !editing" class="font-semibold text-ink-400 transition hover:text-prism-violet">Edit</button>
                            @endcan
                            @can('delete', $post)
                                <form method="POST" action="{{ route('forums.posts.destroy', $post) }}" onsubmit="return confirm('Delete this reply?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="font-semibold text-ink-400 transition hover:text-rose-600">Delete</button>
                                </form>
                            @endcan
                            @auth
                                @if(auth()->id() !== $post->user_id)
                                    <x-report-button type="post" :id="$post->id" />
                                @endif
                            @endauth
                        </footer>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">{{ $posts->links() }}</div>
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
        @if($thread->locked && ! (auth()->check() && auth()->user()->isAdmin()))
            <div class="flex flex-col items-center gap-2 rounded-3xl border-2 border-dashed border-ink-200 bg-ink-50 px-8 py-10 text-center">
                <p class="font-display text-base font-bold text-ink-900">🔒 This thread is locked</p>
                <p class="text-sm text-ink-500">A moderator closed this thread to new replies.</p>
            </div>
        @else
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
        @endif
    </div>
</div>

</x-app-layout>
