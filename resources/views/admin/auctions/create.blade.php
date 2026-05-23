<x-admin-layout heading="New auction" eyebrow="Open a card for bidding">
    <form id="auction-create-form" method="POST" action="{{ route('admin.auctions.store') }}" class="space-y-6">
        @csrf

        <div class="grid gap-6 lg:grid-cols-12">
            {{-- Card selection --}}
            <div class="space-y-4 rounded-3xl border border-ink-200 bg-white p-6 lg:col-span-7">
                <h2 class="font-display text-base font-black">Auction card</h2>
                <p class="text-xs text-ink-500">Pick the physical card you want to open for bidding.</p>
                <x-card-picker />
                @error('card_id')
                    <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Bid configuration --}}
            <aside class="space-y-4 rounded-3xl border border-ink-200 bg-white p-6 lg:col-span-5">
                <h2 class="font-display text-base font-black">Bid settings</h2>

                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Starting bid (Rp)</span>
                    <input type="number" step="500" min="0" name="starting_bid" required
                           value="{{ old('starting_bid', 0) }}"
                           class="mt-1.5 w-full rounded-xl border-ink-200">
                    @error('starting_bid')
                        <p class="text-xs font-semibold text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </label>

                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Bid increment (Rp)</span>
                    <input type="number" step="500" min="500" name="bid_increment" required
                           value="{{ old('bid_increment', 50000) }}"
                           class="mt-1.5 w-full rounded-xl border-ink-200">
                    @error('bid_increment')
                        <p class="text-xs font-semibold text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </label>

                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Buy-now price (Rp) — optional</span>
                    <input type="number" step="500" min="0" name="buy_now_price"
                           value="{{ old('buy_now_price') }}"
                           class="mt-1.5 w-full rounded-xl border-ink-200">
                    @error('buy_now_price')
                        <p class="text-xs font-semibold text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </label>

                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Starts at</span>
                        <input type="datetime-local" name="starts_at" required
                               value="{{ old('starts_at') }}"
                               class="mt-1.5 w-full rounded-xl border-ink-200">
                        @error('starts_at')
                            <p class="text-xs font-semibold text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Ends at</span>
                        <input type="datetime-local" name="ends_at" required
                               value="{{ old('ends_at') }}"
                               class="mt-1.5 w-full rounded-xl border-ink-200">
                        @error('ends_at')
                            <p class="text-xs font-semibold text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </label>
                </div>
            </aside>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.auctions.index') }}"
               class="rounded-full border border-ink-200 px-5 py-2.5 text-sm font-bold">Cancel</a>
            {{-- Opens the confirmation modal instead of submitting directly. --}}
            {{-- <button type="button"
                    @click="$dispatch('open-modal', 'confirm-publish')"
                    class="rounded-full bg-ink-900 px-6 py-2.5 text-sm font-bold text-white hover:bg-ink-700">
                Publish auction
            </button> --}}
            <button type="submit" class="rounded-full bg-ink-900 px-6 py-2.5 text-sm font-bold text-white hover:bg-ink-700">
                Publish auction
            </button>
        </div>
    </form>

    {{-- Publish confirmation --}}
    {{-- <x-modal name="confirm-publish" maxWidth="md">
        <div class="p-6">
            <h3 class="font-display text-lg font-black">Publish this auction?</h3>
            <p class="mt-2 text-sm text-ink-500">
                It will become visible to all users. Double-check the card, starting bid, increment and timing before publishing.
            </p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button"
                        @click="$dispatch('close-modal', 'confirm-publish')"
                        class="rounded-full border border-ink-200 px-5 py-2.5 text-sm font-bold">Cancel</button> --}}
                {{-- The form="" attribute submits the create form from outside it. --}}
                {{-- <button type="submit" form="auction-create-form"
                        class="rounded-full bg-ink-900 px-6 py-2.5 text-sm font-bold text-white hover:bg-ink-700">
                    Publish
                </button>
            </div>
        </div>
    </x-modal> --}}
</x-admin-layout>
