<x-admin-layout heading="Manage auction" eyebrow="Edit & highlight">
    @php
        $rankedBids = $auction->bids->sortByDesc('amount')->values();
    @endphp

    <div class="grid gap-6 lg:grid-cols-12">
        {{-- Auction settings --}}
        <form method="POST" action="{{ route('admin.auctions.update', $auction) }}"
              class="space-y-4 rounded-3xl border border-ink-200 bg-white p-6 lg:col-span-7">
            @csrf
            @method('PATCH')

            <h2 class="font-display text-base font-black">Auction settings</h2>

            {{-- Card is fixed once an auction exists — display only. --}}
            <div class="flex items-center gap-3 rounded-2xl border border-ink-200 bg-ink-50 p-3">
                <span class="inline-flex h-16 w-12 overflow-hidden rounded bg-ink-100">
                    @if($auction->card?->image_small)
                        <img src="{{ $auction->card->image_small }}" alt="" class="h-full w-full object-cover">
                    @endif
                </span>
                <div>
                    <p class="text-sm font-bold">{{ $auction->card?->name ?? '—' }}</p>
                    <p class="text-[11px] text-ink-500">Card cannot be changed after creation</p>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Starting bid (Rp)</span>
                    <input type="number" step="500" min="0" name="starting_bid"
                           value="{{ old('starting_bid', $auction->starting_bid) }}"
                           class="mt-1.5 w-full rounded-xl border-ink-200">
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Bid increment (Rp)</span>
                    <input type="number" step="500" min="1" name="bid_increment"
                           value="{{ old('bid_increment', $auction->bid_increment) }}"
                           class="mt-1.5 w-full rounded-xl border-ink-200">
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Buy-now price (Rp)</span>
                    <input type="number" step="500" min="0" name="buy_now_price"
                           value="{{ old('buy_now_price', $auction->buy_now_price) }}"
                           class="mt-1.5 w-full rounded-xl border-ink-200">
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Status</span>
                    <select name="status" class="mt-1.5 w-full rounded-xl border-ink-200">
                        @foreach(\App\Models\Auction::STATUSES as $s)
                            <option value="{{ $s }}" @selected(old('status', $auction->status) === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Starts at</span>
                    <input type="datetime-local" name="starts_at"
                           value="{{ old('starts_at', $auction->starts_at?->format('Y-m-d\TH:i')) }}"
                           class="mt-1.5 w-full rounded-xl border-ink-200">
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Ends at</span>
                    <input type="datetime-local" name="ends_at"
                           value="{{ old('ends_at', $auction->ends_at?->format('Y-m-d\TH:i')) }}"
                           class="mt-1.5 w-full rounded-xl border-ink-200">
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('admin.auctions.index') }}"
                   class="rounded-full border border-ink-200 px-5 py-2.5 text-sm font-bold">Cancel</a>
                <x-prism-button type="submit" size="md">Save changes</x-prism-button>
            </div>
        </form>

        {{-- Bid management / highlight panel --}}
        <aside class="space-y-4 rounded-3xl border border-ink-200 bg-white p-6 lg:col-span-5"
               x-data="{ pending: null }">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-base font-black">Highlighted bid</h2>
                @php $mode = $auction->highlight_mode ?? 'auto'; @endphp
                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-widest
                    {{ $mode === 'manual' ? 'bg-prism-violet/15 text-prism-violet' : 'bg-emerald-100 text-emerald-700' }}">
                    {{ $mode === 'manual' ? 'Manual override' : 'Auto (highest)' }}
                </span>
            </div>
            <p class="text-xs text-ink-500">
                The highlighted bidder is shown as the winner on the public page. By default the highest bid is highlighted automatically; pick a bid below to override.
            </p>

            {{-- Reset to auto --}}
            @if($mode === 'manual')
                <form method="POST" action="{{ route('admin.auctions.highlight.reset', $auction) }}">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Reset the highlight to the highest bid?')"
                            class="w-full rounded-full border border-ink-200 px-4 py-2 text-xs font-bold hover:bg-ink-50">
                        ↺ Reset to auto (highest wins)
                    </button>
                </form>
            @endif

            {{-- Bid list --}}
            <div class="space-y-1.5">
                @forelse($rankedBids as $i => $bid)
                    @php $isLeader = $bid->user_id === $auction->current_leader_id; @endphp
                    <div class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm
                        {{ $isLeader ? 'border border-prism-violet bg-prism-violet/5' : 'bg-ink-50' }}">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white text-[11px] font-black">
                            {{ $isLeader ? '👑' : $i + 1 }}
                        </span>
                        <div class="min-w-0">
                            <p class="truncate font-bold">{{ $bid->user?->name ?? 'Anonymous' }}</p>
                            <p class="text-[10px] text-ink-400">{{ $bid->created_at?->diffForHumans() }}</p>
                        </div>
                        <span class="ml-auto font-mono font-bold">@idr($bid->amount)</span>
                        @if($isLeader)
                            <span class="rounded-full bg-prism-violet/15 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-prism-violet">
                                Highlighted
                            </span>
                        @else
                            {{-- @js() emits a correctly JS- and HTML-attribute-escaped string,
                                 so usernames containing quotes are handled safely. --}}
                            <button type="button"
                                    @click="pending = {
                                        url: '{{ route('admin.auctions.highlight', $auction) }}',
                                        bidId: {{ $bid->id }},
                                        label: @js('Highlight ' . ($bid->user?->name ?? 'this bidder') . ' as the winner?')
                                    }"
                                    class="rounded-full bg-ink-900 px-3 py-1 text-[10px] font-bold text-white hover:bg-ink-700">
                                Highlight
                            </button>
                        @endif
                    </div>
                @empty
                    <p class="rounded-xl bg-ink-50 px-3 py-6 text-center text-sm text-ink-500">No bids yet.</p>
                @endforelse
            </div>

            {{-- Shared highlight confirmation modal (one modal, action bound at click time) --}}
            <div x-show="pending" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center bg-ink-900/70 px-4"
                 @keydown.escape.window="pending = null">
                <div @click.outside="pending = null" class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
                    <h3 class="font-display text-lg font-black">Change highlighted bid?</h3>
                    <p class="mt-2 text-sm text-ink-500" x-text="pending?.label"></p>
                    <p class="mt-1 text-xs text-ink-400">This switches the highlight to Manual override.</p>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="pending = null"
                                class="rounded-full border border-ink-200 px-5 py-2.5 text-sm font-bold">Cancel</button>
                        <form method="POST" :action="pending?.url">
                            @csrf
                            <input type="hidden" name="bid_id" :value="pending?.bidId">
                            <button type="submit"
                                    class="rounded-full bg-ink-900 px-6 py-2.5 text-sm font-bold text-white hover:bg-ink-700">
                                Highlight bidder
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</x-admin-layout>
