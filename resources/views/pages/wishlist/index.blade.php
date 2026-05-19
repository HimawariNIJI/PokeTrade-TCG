<x-app-layout>

<section class="mx-auto max-w-[1400px] px-4 py-16 md:px-8">
    <div class="mb-10 flex flex-wrap items-end justify-between gap-6">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-ink-200 px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-ink-700">
                Saved for later
            </span>
            <h1 class="mt-3 font-display text-4xl font-black tracking-tight md:text-5xl">
                My <span class="prism-text">wishlist</span>.
            </h1>
            <p class="mt-2 text-sm text-ink-700">{{ $cards->total() }} card{{ $cards->total() === 1 ? '' : 's' }} saved.</p>
        </div>
    </div>

    @if($cards->isEmpty())
        <x-empty-state
            icon="❤"
            title="No cards saved yet"
            message="Tap the heart on any card detail page to add it to your wishlist.">
            <x-prism-button :href="route('cards.index')" size="md">Browse cards</x-prism-button>
        </x-empty-state>
    @else
        <div class="grid grid-cols-2 gap-x-5 gap-y-10 md:grid-cols-4 lg:grid-cols-6">
            @foreach($cards as $card)
    <div class="relative" x-data="{ confirmRemove: false }">
        <button
            @click="confirmRemove = true"
            class="absolute right-2 top-2 z-20 rounded-full bg-rose-500 px-3 py-1 text-[11px] font-bold text-white shadow-lg transition hover:scale-105 hover:bg-rose-600">
            ✕
        </button>
        <div
            x-show="confirmRemove"
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            style="display: none;">
            <div
                @click.outside="confirmRemove = false"
                class="w-full max-w-sm rounded-3xl bg-white p-6 shadow-2xl">
                <h2 class="font-display text-2xl font-black text-ink-900">
                    Remove card?
                </h2>
                <p class="mt-2 text-sm text-ink-600">
                    Are you sure you want to remove
                    <span class="font-semibold">{{ $card->name }}</span>
                    from your wishlist?
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button
                        @click="confirmRemove = false"
                        class="rounded-full border border-ink-200 px-4 py-2 text-sm font-semibold text-ink-700 hover:bg-ink-100">
                        Cancel
                    </button>
                    <form method="POST"
                          action="{{ route('wishlist.toggle', $card) }}">
                        @csrf

                        <button
                            type="submit"
                            class="rounded-full bg-rose-500 px-5 py-2 text-sm font-bold text-white hover:bg-rose-600">
                            Remove
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <x-card-tile :card="$card" />
    </div>
@endforeach
        </div>
        <div class="mt-10">{{ $cards->links() }}</div>
    @endif
</section>

</x-app-layout>
