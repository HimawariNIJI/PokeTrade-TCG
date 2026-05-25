<x-admin-layout heading="Manage auction" eyebrow="Edit & highlight">
    @php
        $topBids = $auction->bids->sortByDesc('amount')->values()->take(3);
    @endphp

    @if ($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 mb-6">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
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
                    <input type="number" step="500" min="500" name="bid_increment"
                           value="{{ old('bid_increment', $auction->bid_increment) }}"
                           class="mt-1.5 w-full rounded-xl border-ink-200">
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Buy-now price (Rp)</span>
                    <input type="number" step="500" min="0" name="buy_now_price"
                           value="{{ old('buy_now_price', $auction->buy_now_price) }}"
                           class="mt-1.5 w-full rounded-xl border-ink-200">
                </label>
                @php
                    $hasBids = $auction->bids()->exists();
                    $statusOptions = match($auction->status) {
                        'scheduled' => ['scheduled','live','cancelled'],
                        'live' => $hasBids ? ['live','ended'] : ['live','ended','cancelled'],
                        'ended' => ['ended'],
                        'cancelled' => ['cancelled','scheduled','live','ended'],
                        default => [$auction->status]
                    };
                @endphp
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Status</span>
                    <select name="status" class="mt-1.5 w-full rounded-xl border-ink-200" {{ $auction->status === 'ended' ? 'disabled' : '' }}>
                        @foreach($statusOptions as $s)
                            <option value="{{ $s }}" @selected(old('status', $auction->status) === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    @if($auction->status === 'ended')
                        <p class="mt-1 text-xs text-ink-500">
                            Ended auctions cannot be modified.
                        </p>
                    @elseif($auction->status === 'live' && $hasBids)
                        <p class="mt-1 text-xs text-ink-500">
                            Live auctions with bids cannot be cancelled.
                        </p>
                    @endif
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

            {{-- Listing highlight toggle --}}
            <label class="flex items-start gap-3 rounded-2xl border border-prism-pink/30 bg-prism-pink/5 p-4">
                <input type="checkbox" name="is_highlighted" value="1"
                       @checked(old('is_highlighted', $auction->is_highlighted))
                       class="mt-0.5 rounded border-ink-300 text-prism-pink focus:ring-prism-pink">
                <span>
                    <span class="text-sm font-bold">⚡ Highlight this auction on the public listing</span>
                    <span class="mt-0.5 block text-xs text-ink-500">
                        Features this auction in the hero banner at the top of the /auctions page.
                        Only one auction is highlighted at a time. Leave this off to let the live
                        auction with the highest current bid be featured automatically.
                    </span>
                </span>
            </label>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('admin.auctions.index') }}"
                   class="rounded-full border border-ink-200 px-5 py-2.5 text-sm font-bold">Cancel</a>
                <x-prism-button type="submit" size="md">Save changes</x-prism-button>
            </div>
        </form>

        {{-- Top bidders + refund console --}}
        <aside class="space-y-6 lg:col-span-5">
            {{-- Refund console — only shows when there's something to act on. --}}
            @if($auction->refund_status !== 'none' || ($auction->status === 'ended' && $auction->winner_id))
                <div class="rounded-3xl border border-ink-200 bg-white p-6">
                    <h2 class="font-display text-base font-black">Refund</h2>

                    @if($auction->refund_status === 'requested')
                        <div class="mt-3 space-y-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                            <p class="font-bold">{{ $auction->winner?->name ?? 'Winner' }} requested a refund</p>
                            <p class="text-xs text-amber-700">
                                Submitted {{ $auction->refund_requested_at?->diffForHumans() }} ·
                                Winning bid @idr($auction->winning_amount ?? $auction->current_bid)
                            </p>
                            <p class="rounded-xl bg-white px-3 py-2 text-xs text-ink-900">
                                "{{ $auction->refund_reason }}"
                            </p>
                            <form method="POST" action="{{ route('admin.auctions.resolveRefund', $auction) }}" class="flex gap-2 pt-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" name="decision" value="approved"
                                        class="flex-1 rounded-full bg-emerald-600 px-4 py-2 text-xs font-black uppercase tracking-widest text-white hover:bg-emerald-700">
                                    Approve
                                </button>
                                <button type="submit" name="decision" value="rejected"
                                        class="flex-1 rounded-full bg-rose-600 px-4 py-2 text-xs font-black uppercase tracking-widest text-white hover:bg-rose-700">
                                    Reject
                                </button>
                            </form>
                        </div>
                    @elseif($auction->refund_status === 'approved')
                        <p class="mt-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
                            Refund approved {{ $auction->refund_resolved_at?->diffForHumans() }}
                        </p>
                    @elseif($auction->refund_status === 'rejected')
                        <p class="mt-3 rounded-2xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800">
                            Refund rejected {{ $auction->refund_resolved_at?->diffForHumans() }}
                        </p>
                    @elseif($auction->winner_id && ! $auction->winner_paid_at)
                        <p class="mt-3 rounded-2xl border border-ink-200 bg-ink-50 p-3 text-sm text-ink-700">
                            Winner {{ $auction->winner?->name }} has not yet paid.
                        </p>
                    @elseif($auction->winner_paid_at)
                        <p class="mt-3 rounded-2xl border border-ink-200 bg-ink-50 p-3 text-sm text-ink-700">
                            {{ $auction->winner?->name }} paid {{ $auction->winner_paid_at->diffForHumans() }}. No refund requested.
                        </p>
                    @endif
                </div>
            @endif

            {{-- Top bidders (read-only) --}}
            <div class="space-y-4 rounded-3xl border border-ink-200 bg-white p-6">
                <h2 class="font-display text-base font-black">Top bidders</h2>
                <p class="text-xs text-ink-500">
                    The three highest bids on this auction. The leading bidder wins when the timer ends.
                </p>
                <div class="space-y-1.5">
                    @forelse($topBids as $i => $bid)
                        <div class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm
                            {{ $i === 0 ? 'border border-prism-violet bg-prism-violet/5' : 'bg-ink-50' }}">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white text-[11px] font-black">
                                {{ $i === 0 ? '👑' : $i + 1 }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate font-bold">{{ $bid->user?->name ?? 'Anonymous' }}</p>
                                <p class="text-[10px] text-ink-400">{{ $bid->created_at?->diffForHumans() }}</p>
                            </div>
                            <span class="ml-auto font-mono font-bold">@idr($bid->amount)</span>
                        </div>
                    @empty
                        <p class="rounded-xl bg-ink-50 px-3 py-6 text-center text-sm text-ink-500">No bids yet.</p>
                    @endforelse
                </div>
            </div>
        </aside>
    </div>
</x-admin-layout>
