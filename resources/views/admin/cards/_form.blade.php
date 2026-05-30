<form method="POST" action="{{ route('admin.cards.update', $card) }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PATCH')

    <div class="grid gap-6 lg:grid-cols-12">
        <div class="lg:col-span-7 space-y-4 rounded-3xl border border-ink-200 bg-white p-6">
            <h2 class="font-display text-base font-black">Card information</h2>

            {{-- Card source: dropdown of API-sourced cards (per team decision) --}}
            <label class="block">
                <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Source card (from API catalog)</span>
                <select name="api_id" class="mt-1.5 w-full rounded-xl border-ink-200 focus:border-prism-violet focus:ring-prism-violet">
                    <option value="">— Pick a Prismatic Evolutions card —</option>
                    {{-- TODO(team-backend): pass $apiCards from controller, or use AJAX search --}}
                </select>
                <span class="text-[11px] text-ink-500">Per the team decision, cards come from the dropdown rather than direct image upload.</span>
            </label>

            <div class="grid gap-4 md:grid-cols-2">
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Name</span>
                    <input type="text" name="name" value="{{ old('name', $card?->name) }}" class="mt-1.5 w-full rounded-xl border-ink-200">
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Card number</span>
                    <input type="text" name="number" value="{{ old('number', $card?->number) }}" class="mt-1.5 w-full rounded-xl border-ink-200">
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Rarity</span>
                    <input type="text" name="rarity" value="{{ old('rarity', $card?->rarity) }}" class="mt-1.5 w-full rounded-xl border-ink-200">
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Supertype</span>
                    <select name="supertype" class="mt-1.5 w-full rounded-xl border-ink-200">
                        @foreach(['Pokémon', 'Trainer', 'Energy'] as $st)
                            <option value="{{ $st }}" @selected(old('supertype', $card?->supertype) === $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <label class="block">
                <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Description (flavor text)</span>
                <textarea name="flavor_text" rows="3" class="mt-1.5 w-full rounded-xl border-ink-200">{{ old('flavor_text', $card?->flavor_text) }}</textarea>
            </label>
        </div>

        <aside class="lg:col-span-5 space-y-4">
            <div class="rounded-3xl border border-ink-200 bg-white p-6">
                <h2 class="font-display text-base font-black">Pricing &amp; stock</h2>
                <div class="mt-3 grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Price (Rp)</span>
                        <input type="number" step="500" name="price" value="{{ old('price', $card?->price ?? 0) }}" class="mt-1.5 w-full rounded-xl border-ink-200">
                    </label>
                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Market price (Rp)</span>
                        <input type="number" step="500" name="market_price" value="{{ old('market_price', $card?->market_price ?? 0) }}" class="mt-1.5 w-full rounded-xl border-ink-200">
                    </label>
                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Stock</span>
                        <input type="number" name="stock" value="{{ old('stock', $card?->stock ?? 0) }}" class="mt-1.5 w-full rounded-xl border-ink-200">
                    </label>
                </div>

                <label class="mt-4 inline-flex cursor-pointer items-center gap-2">
                    <input type="checkbox" name="featured" value="1" @checked(old('featured', $card?->featured)) class="rounded border-ink-300 text-prism-violet focus:ring-prism-violet">
                    <span class="text-sm font-semibold">Featured on home page</span>
                </label>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.cards.index') }}" class="rounded-full border border-ink-200 px-5 py-2.5 text-sm font-bold">Cancel</a>
                <x-prism-button type="submit" size="md">{{ $card ? 'Save changes' : 'Create card' }}</x-prism-button>
            </div>
        </aside>
    </div>
</form>
