<x-app-layout>

@php
    // Is the signed-in trainer looking at their own wall? Drives the
    // "Edit profile" affordance in the header.
    $isOwner = auth()->check() && auth()->id() === $user->id;

    // Social links worth rendering — drop the empty slots up front so
    // the row only appears when there's actually something to show.
    $socials = collect($user->social_links ?? [])->filter(fn ($url) => filled($url));

    // Icon + label metadata per platform. Icons are simple inline SVG
    // paths so we don't pull in an icon library.
    $socialMeta = [
        'twitter'   => ['label' => 'Twitter / X'],
        'instagram' => ['label' => 'Instagram'],
        'tiktok'    => ['label' => 'TikTok'],
        'youtube'   => ['label' => 'YouTube'],
        'discord'   => ['label' => 'Discord'],
        'website'   => ['label' => 'Website'],
    ];
@endphp

{{-- ============================================================
     Header banner — avatar, name, location, member-since.
     ============================================================ --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-ink-900 via-prism-violet/20 to-ink-900"></div>
    <div class="absolute inset-0 -z-10 halftone opacity-10"></div>

    <div class="mx-auto max-w-[1400px] px-4 py-20 md:px-8">
        <div class="flex flex-col items-start gap-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-5">
                {{-- Avatar fallback — first initial in a prism circle
                     (same pattern as the nav user-menu). --}}
                <span class="inline-flex h-20 w-20 items-center justify-center rounded-full prism-bg text-2xl font-black text-white shadow-2xl ring-4 ring-white/20">
                    {{ Str::upper(Str::substr($user->name, 0, 1)) }}
                </span>

                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-white backdrop-blur">
                        <span class="h-2 w-2 rounded-full bg-prism-mint"></span>
                        Trainer
                    </span>
                    <h1 class="mt-2 font-display text-4xl font-black leading-tight text-white md:text-5xl">
                        {{ $user->name }}
                    </h1>
                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-white/70">
                        @if($user->location)
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                                </svg>
                                {{ $user->location }}
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1.5 font-mono text-xs">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0V11.25A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                            </svg>
                            Member since {{ $user->created_at->format('M Y') }}
                        </span>
                    </div>
                </div>
            </div>

            @if($isOwner)
                <x-prism-button :href="route('settings.edit')" variant="ghost" size="md">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                    </svg>
                    Edit profile
                </x-prism-button>
            @endif
        </div>
    </div>
</section>

<div class="mx-auto max-w-[1400px] space-y-16 px-4 py-20 md:px-8">

    {{-- ========================================================
         Bio — gated on show_bio + a non-empty bio.
         ======================================================== --}}
    @if($user->shows('show_bio') && filled($user->bio))
        <section>
            <x-section-heading eyebrow="About" title="The trainer's story" />
            <p class="mt-5 max-w-3xl whitespace-pre-line text-base leading-relaxed text-ink-700">
                {{ $user->bio }}
            </p>
        </section>
    @endif

    {{-- ========================================================
         Social links row — gated on show_socials.
         ======================================================== --}}
    @if($user->shows('show_socials') && $socials->isNotEmpty())
        <section>
            <x-section-heading eyebrow="Elsewhere" title="Find this trainer" />
            <div class="mt-5 flex flex-wrap gap-3">
                @foreach($socials as $platform => $url)
                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                       class="group inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white px-4 py-2 text-sm font-semibold text-ink-700 transition hover:border-prism-violet hover:text-prism-violet">
                        <span class="h-2 w-2 rounded-full prism-bg"></span>
                        {{ $socialMeta[$platform]['label'] ?? Str::title($platform) }}
                        <svg class="h-3.5 w-3.5 text-ink-300 transition group-hover:text-prism-violet" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                        </svg>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ========================================================
         Digital collection — gacha cards, gated on show_collection.
         ======================================================== --}}
    @if($user->shows('show_collection'))
        <section>
            <div class="flex items-end justify-between gap-4">
                <x-section-heading eyebrow="Vault" title="Digital collection" />
                <span class="shrink-0 rounded-full border border-ink-200 bg-white px-4 py-1.5 font-mono text-sm font-bold text-ink-900">
                    {{ $digitalCount }} {{ Str::plural('card', $digitalCount) }}
                </span>
            </div>

            @if($digitalCards->isNotEmpty())
                <div class="mt-8 grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                    @foreach($digitalCards as $card)
                        <x-collection-card :card="$card" />
                    @endforeach
                </div>
                @if($digitalCount > $digitalCards->count())
                    <p class="mt-6 text-sm text-ink-500">
                        Showing the {{ $digitalCards->count() }} most recent pulls of {{ $digitalCount }} total.
                    </p>
                @endif
            @else
                <div class="mt-8">
                    <x-empty-state
                        icon="◇"
                        title="No cards pulled yet"
                        message="This trainer hasn't opened any digital packs. The vault is waiting." />
                </div>
            @endif
        </section>
    @endif

    {{-- ========================================================
         Chase cards — the wishlist, gated on show_chase.
         ======================================================== --}}
    @if($user->shows('show_chase'))
        <section>
            <div class="flex items-end justify-between gap-4">
                <x-section-heading eyebrow="The hunt" title="Chase cards" />
                <span class="shrink-0 rounded-full border border-ink-200 bg-white px-4 py-1.5 font-mono text-sm font-bold text-ink-900">
                    {{ $chaseCount }} {{ Str::plural('card', $chaseCount) }}
                </span>
            </div>

            @if($chaseCards->isNotEmpty())
                <div class="mt-8 grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                    @foreach($chaseCards as $card)
                        <x-card-tile :card="$card" />
                    @endforeach
                </div>
            @else
                <div class="mt-8">
                    <x-empty-state
                        icon="✦"
                        title="No cards on the hunt"
                        message="This trainer isn't chasing anything right now." />
                </div>
            @endif
        </section>
    @endif

    {{-- ========================================================
         Comment wall — always visible. The form is gated on
         allow_comments + an authenticated viewer.
         ======================================================== --}}
    <section>
        <x-section-heading
            eyebrow="Wall"
            title="Trainer wall"
            subtitle="Drop a note for {{ $user->name }}." />

        {{-- Flash from a just-posted comment. --}}
        @if(session('status'))
            <div class="mt-5 rounded-2xl border border-prism-mint/40 bg-prism-mint/10 px-4 py-3 text-sm font-semibold text-ink-900">
                {{ session('status') }}
            </div>
        @endif

        {{-- Comment composer — only when the wall is open and the
             viewer is signed in. --}}
        @if($user->shows('allow_comments'))
            @auth
                <form method="POST" action="{{ route('profiles.comment', $user) }}" class="mt-6">
                    @csrf
                    <div class="rounded-3xl border border-ink-200 bg-white p-4">
                        <textarea name="body" rows="3" maxlength="500" required
                                  placeholder="Say something nice…"
                                  class="block w-full resize-none rounded-2xl border-ink-200 text-sm focus:border-prism-violet focus:ring-prism-violet">{{ old('body') }}</textarea>
                        @error('body')
                            <p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                        <div class="mt-3 flex justify-end">
                            <x-prism-button type="submit" size="sm">Post comment</x-prism-button>
                        </div>
                    </div>
                </form>
            @else
                <p class="mt-6 text-sm text-ink-500">
                    <a href="{{ route('login') }}" class="font-semibold text-prism-violet hover:underline">Log in</a>
                    to leave a comment.
                </p>
            @endauth
        @else
            <p class="mt-6 rounded-2xl border border-dashed border-ink-200 bg-ink-50 px-4 py-3 text-sm text-ink-500">
                Comments are turned off for this trainer.
            </p>
        @endif

        {{-- The wall itself. --}}
        <div class="mt-8 space-y-4">
            @forelse($comments as $comment)
                <div class="flex gap-4 rounded-3xl border border-ink-200 bg-white p-5">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full prism-bg text-sm font-bold text-white">
                        {{ Str::upper(Str::substr(optional($comment->author)->name ?? '?', 0, 1)) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-baseline justify-between gap-x-3">
                            @if($comment->author)
                                <a href="{{ route('profiles.show', $comment->author) }}"
                                   class="font-display text-sm font-bold text-ink-900 hover:text-prism-violet">
                                    {{ $comment->author->name }}
                                </a>
                            @else
                                <span class="font-display text-sm font-bold text-ink-500">Deleted trainer</span>
                            @endif
                            <span class="font-mono text-xs text-ink-500">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="mt-1.5 whitespace-pre-line text-sm leading-relaxed text-ink-700">
                            {{ $comment->body }}
                        </p>
                    </div>
                </div>
            @empty
                <x-empty-state
                    icon="✎"
                    title="No comments yet"
                    message="Be the first to sign {{ $user->name }}'s wall." />
            @endforelse
        </div>
    </section>

</div>

</x-app-layout>
