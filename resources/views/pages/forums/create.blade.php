<x-app-layout>

{{-- ── Header ──────────────────────────────────────────────────── --}}
<section class="relative isolate overflow-hidden bg-ink-900">
    <img src="{{ asset('images/gacha-banner.png') }}" alt=""
         class="pointer-events-none absolute inset-0 -z-10 h-full w-full object-cover object-right select-none"
         loading="eager" fetchpriority="high">
    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-r from-ink-900/90 via-ink-900/55 to-transparent"></div>
    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-b from-ink-900/70 via-transparent to-ink-900/40 md:hidden"></div>
    <div class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-24 bg-gradient-to-t from-ink-900 to-transparent"></div>
    <div class="pointer-events-none absolute inset-0 -z-10 halftone opacity-10"></div>
    <x-prism-aurora />
    <div class="pointer-events-none absolute inset-x-0 top-0 z-10 h-px prism-bg"></div>

    <div class="mx-auto max-w-[760px] px-4 pb-12 pt-16 md:px-8 md:pt-20">
        <nav class="mb-4 flex items-center gap-2 text-xs font-semibold text-white/60">
            <a href="{{ route('forums.index') }}" class="transition hover:text-white">Forums</a>
            <span>/</span>
            <span class="text-white">New thread</span>
        </nav>
        <h1 class="font-display text-4xl font-black tracking-tight text-white md:text-5xl">
            Start a <span class="prism-text">thread</span>.
        </h1>
        <p class="mt-3 max-w-xl text-white/70">
            Pick a board, give it a clear title, and say your piece. Keep it friendly — collectors helping collectors.
        </p>
    </div>
</section>

<div class="mx-auto max-w-[760px] px-4 py-12 md:px-8">
    <div class="relative overflow-hidden rounded-3xl border border-ink-200 bg-white p-6 shadow-sm md:p-8">
        <span class="absolute inset-x-0 top-0 h-1 prism-bg"></span>

        <form method="POST" action="{{ route('forums.store') }}" class="space-y-6">
            @csrf

            {{-- Category --}}
            <div>
                <label for="forum_category_id" class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-ink-700">
                    Board
                </label>
                @if($categories->isNotEmpty())
                    <select id="forum_category_id" name="forum_category_id" required
                            class="w-full rounded-2xl border-ink-200 bg-ink-50 px-4 py-3 text-sm text-ink-900 focus:border-prism-violet focus:ring-prism-violet">
                        <option value="" disabled {{ old('forum_category_id') ? '' : 'selected' }}>Choose a board…</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('forum_category_id') == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <p class="rounded-2xl border border-dashed border-ink-200 bg-ink-50 px-4 py-3 text-sm text-ink-500">
                        No boards are available yet — a thread can't be posted.
                    </p>
                @endif
                @error('forum_category_id')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Title --}}
            <div>
                <label for="title" class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-ink-700">
                    Title
                </label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required maxlength="160"
                       placeholder="What's this thread about?"
                       class="w-full rounded-2xl border-ink-200 bg-ink-50 px-4 py-3 text-sm placeholder:text-ink-500 focus:border-prism-violet focus:ring-prism-violet">
                @error('title')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Body --}}
            <div>
                <label for="body" class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-ink-700">
                    Message
                </label>
                <textarea id="body" name="body" rows="8" required maxlength="10000"
                          placeholder="Write your post…"
                          class="w-full rounded-2xl border-ink-200 bg-ink-50 px-4 py-3 text-sm placeholder:text-ink-500 focus:border-prism-violet focus:ring-prism-violet">{{ old('body') }}</textarea>
                @error('body')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 border-t border-ink-100 pt-6">
                <x-prism-button :href="route('forums.index')" variant="ghost" size="sm">Cancel</x-prism-button>
                <x-prism-button type="submit" :disabled="$categories->isEmpty()">
                    Post thread
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0-6-6m6 6-6 6"/>
                    </svg>
                </x-prism-button>
            </div>
        </form>
    </div>
</div>

</x-app-layout>
