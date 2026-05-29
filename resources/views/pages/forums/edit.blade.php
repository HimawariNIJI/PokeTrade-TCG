<x-app-layout title="Edit thread">

{{-- ── Header ──────────────────────────────────────────────────── --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-ink-900 via-prism-violet/20 to-ink-900"></div>
    <div class="absolute inset-0 -z-10 halftone opacity-10"></div>
    <div class="absolute inset-x-0 top-0 -z-10 h-px prism-bg"></div>

    <div class="mx-auto max-w-[760px] px-4 pb-12 pt-16 md:px-8 md:pt-20">
        <nav class="mb-4 flex items-center gap-2 text-xs font-semibold text-white/60">
            <a href="{{ route('forums.index') }}" class="transition hover:text-white">Forums</a>
            <span>/</span>
            <a href="{{ route('forums.thread', $thread) }}" class="line-clamp-1 transition hover:text-white">{{ $thread->title }}</a>
            <span>/</span>
            <span class="text-white">Edit</span>
        </nav>
        <h1 class="font-display text-4xl font-black tracking-tight text-white md:text-5xl">
            Edit <span class="prism-text">thread</span>.
        </h1>
    </div>
</section>

<div class="mx-auto max-w-[760px] px-4 py-12 md:px-8">
    <div class="relative overflow-hidden rounded-3xl border border-ink-200 bg-white p-6 shadow-sm md:p-8">
        <span class="absolute inset-x-0 top-0 h-1 prism-bg"></span>

        <form method="POST" action="{{ route('forums.update', $thread) }}" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Category --}}
            <div>
                <label for="forum_category_id" class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-ink-700">
                    Board
                </label>
                <select id="forum_category_id" name="forum_category_id" required
                        class="w-full rounded-2xl border-ink-200 bg-ink-50 px-4 py-3 text-sm text-ink-900 focus:border-prism-violet focus:ring-prism-violet">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('forum_category_id', $thread->forum_category_id) == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('forum_category_id')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Title --}}
            <div>
                <label for="title" class="mb-1.5 block text-xs font-bold uppercase tracking-widest text-ink-700">
                    Title
                </label>
                <input type="text" id="title" name="title" value="{{ old('title', $thread->title) }}" required maxlength="160"
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
                          class="w-full rounded-2xl border-ink-200 bg-ink-50 px-4 py-3 text-sm placeholder:text-ink-500 focus:border-prism-violet focus:ring-prism-violet">{{ old('body', $thread->body) }}</textarea>
                @error('body')
                    <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 border-t border-ink-100 pt-6">
                <x-prism-button :href="route('forums.thread', $thread)" variant="ghost" size="sm">Cancel</x-prism-button>
                <x-prism-button type="submit">Save changes</x-prism-button>
            </div>
        </form>
    </div>
</div>

</x-app-layout>
