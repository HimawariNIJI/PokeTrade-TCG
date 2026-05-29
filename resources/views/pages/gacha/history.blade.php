<x-app-layout>

{{-- =====================================================
     PULL HISTORY — every gacha pull as its own entry,
     sorted earliest-first. Duplicate pulls are kept as
     separate rows (no deduplication).
     ===================================================== --}}
<section class="mx-auto max-w-[1100px] px-4 py-16 md:px-8">
    <div class="mb-10 flex flex-wrap items-end justify-between gap-6">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-ink-200 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-ink-700">
                Pull history
            </span>
            <h1 class="mt-3 font-display text-4xl font-black tracking-tight md:text-5xl">
                Every <span class="prism-text">pull</span>, in order.
            </h1>
            <p class="mt-2 text-sm text-ink-700">
                {{ $pulls->total() }} pull{{ $pulls->total() === 1 ? '' : 's' }} ·
                earliest first · duplicates kept as separate entries.
            </p>
        </div>

        <x-prism-button :href="route('collection.index')" variant="ghost" size="md">Back to collection</x-prism-button>
    </div>

    @if($pulls->total() > 0)
        <ol class="overflow-hidden rounded-2xl border border-ink-200 bg-white">
            @foreach($pulls as $pull)
                @php($when = $pull->obtained_at ?? $pull->created_at)
                <li class="flex items-center gap-4 border-b border-ink-100 px-4 py-3 last:border-b-0 sm:px-5">
                    <span class="w-10 shrink-0 text-right font-mono text-xs text-ink-400">
                        #{{ $pulls->firstItem() + $loop->index }}
                    </span>

                    <img src="{{ $pull->card->image_small }}" alt="{{ $pull->card->name }}"
                        class="h-16 w-12 shrink-0 rounded-md object-cover ring-1 ring-ink-100" />

                    <div class="min-w-0 flex-1">
                        <p class="line-clamp-1 font-display text-sm font-bold text-ink-900">{{ $pull->card->name }}</p>
                        <div class="mt-0.5 flex flex-wrap items-center gap-2 text-[11px] text-ink-500">
                            <span class="uppercase tracking-wider">{{ $pull->card->rarity ?: 'Common' }}</span>
                            <span class="text-ink-300">·</span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-ink-50 px-2 py-0.5 font-semibold uppercase tracking-wider text-ink-600">
                                {{ $pull->source }}
                            </span>
                        </div>
                    </div>

                    <time class="shrink-0 text-right text-xs text-ink-500"
                        datetime="{{ optional($when)->toIso8601String() }}">
                        {{ $when ? $when->format('M j, Y') : '—' }}
                        <span class="block text-[11px] text-ink-400">{{ $when ? $when->format('g:i A') : '' }}</span>
                    </time>
                </li>
            @endforeach
        </ol>

        <div class="mt-10">{{ $pulls->links() }}</div>
    @else
        <x-empty-state
            icon="✦"
            title="No pulls yet"
            message="Pull a digital pack and every card you draw will be logged here, oldest first.">
            <x-prism-button :href="route('gacha.index')" size="md">Pull your first pack</x-prism-button>
        </x-empty-state>
    @endif
</section>

</x-app-layout>
