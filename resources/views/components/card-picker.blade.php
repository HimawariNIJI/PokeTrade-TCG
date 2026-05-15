@props(['selected' => null])

{{--
  Card picker — a search-driven modal for choosing a catalogue card.
  Renders a hidden <input name="card_id"> consumed by the host <form>.
  `selected` (optional) pre-fills the picker in edit mode; pass an object
  exposing id, name, image_small, set_name.
--}}
<div
    x-data="cardPicker(@js($selected ? [
        'id' => $selected->id,
        'name' => $selected->name,
        'image_small' => $selected->image_small,
        'set_name' => $selected->set_name ?? null,
    ] : null))"
    class="space-y-3"
>
    <input type="hidden" name="card_id" :value="picked?.id ?? ''">

    {{-- Chosen-card display --}}
    <template x-if="picked">
        <div class="flex items-center gap-3 rounded-2xl border border-ink-200 bg-white p-3">
            <img :src="picked.image_small" alt="" class="h-16 w-12 rounded object-cover bg-ink-100">
            <div class="min-w-0">
                <p class="truncate text-sm font-bold" x-text="picked.name"></p>
                <p class="truncate text-[11px] text-ink-500" x-text="picked.set_name"></p>
            </div>
            <button type="button" @click="open()" class="ml-auto text-xs font-bold text-prism-violet hover:underline">
                Change
            </button>
        </div>
    </template>

    {{-- Empty state / trigger --}}
    <template x-if="!picked">
        <button
            type="button"
            @click="open()"
            class="flex w-full items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-ink-300 px-4 py-6 text-sm font-bold text-ink-500 transition hover:border-prism-violet hover:text-prism-violet"
        >
            ◆ Choose Card from catalogue
        </button>
    </template>

    {{-- Picker modal --}}
    <div
        x-show="modal"
        x-cloak
        @keydown.escape.window="modal = false"
        class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-ink-900/70 px-4 py-10"
    >
        <div @click.outside="modal = false" class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-lg font-black">Choose a card</h3>
                <button type="button" @click="modal = false" class="text-2xl leading-none text-ink-400 hover:text-ink-900">&times;</button>
            </div>

            <input
                type="search"
                x-model="q"
                @input.debounce.300ms="search()"
                placeholder="Search 2,000+ cards by name…"
                class="mt-4 w-full rounded-full border-ink-200 text-sm focus:border-prism-violet focus:ring-prism-violet"
            >

            <div class="mt-4 grid max-h-[55vh] grid-cols-2 gap-3 overflow-y-auto sm:grid-cols-3">
                <template x-for="card in results" :key="card.id">
                    <button
                        type="button"
                        @click="choose(card)"
                        class="rounded-2xl border border-ink-200 p-2 text-left transition hover:border-prism-violet hover:shadow-lg"
                    >
                        <img :src="card.image_small" alt="" class="aspect-[3/4] w-full rounded-lg object-cover bg-ink-100">
                        <p class="mt-1.5 truncate text-xs font-bold" x-text="card.name"></p>
                        <p class="truncate text-[10px] text-ink-500" x-text="card.rarity"></p>
                    </button>
                </template>

                <template x-if="!loading && results.length === 0">
                    <p class="col-span-full py-10 text-center text-sm text-ink-500">No cards found.</p>
                </template>
            </div>

            <p x-show="loading" class="mt-3 text-center text-xs text-ink-500">Searching…</p>
        </div>
    </div>
</div>
