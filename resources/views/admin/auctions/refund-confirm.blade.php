<x-admin-layout heading="Refund Auction" eyebrow="Auction {{ $auction->id }}">
    <x-slot:actions>
        <a href="{{ route('admin.auctions.index') }}" class="text-xs font-semibold text-ink-500 hover:text-ink-900">← Back</a>
    </x-slot:actions>

    <div class="max-w-2xl">
        {{-- Card Info --}}
        <div class="rounded-3xl border border-ink-200 bg-white p-6">
            <div class="flex items-center gap-4">
                <span class="inline-flex h-16 w-12 overflow-hidden rounded bg-ink-100">
                    @if($auction->card?->image_small)
                        <img src="{{ $auction->card->image_small }}" alt="" class="h-full w-full object-cover">
                    @endif
                </span>
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-ink-500">{{ $auction->card?->name ?? '—' }}</p>
                    <h2 class="font-display text-xl font-black">Refund All Losers</h2>
                    <p class="text-sm text-ink-500">{{ count($loserBids) }} {{ count($loserBids) === 1 ? 'bidder' : 'bidders' }} to refund</p>
                </div>
            </div>
        </div>

        {{-- Loser Bids List --}}
        @if($loserBids->count() > 0)
            <div class="mt-6 rounded-3xl border border-ink-200 bg-white">
                <div class="px-6 py-4 border-b border-ink-100">
                    <p class="text-sm font-bold text-ink-700">Non-winning bidders:</p>
                </div>
                <div class="divide-y divide-ink-100">
                    @foreach($loserBids as $bid)
                        <div class="flex items-center justify-between px-6 py-3 hover:bg-ink-50">
                            <span class="font-medium">{{ $bid->user?->name ?? 'Anonymous' }}</span>
                            <span class="font-mono font-bold text-ink-500">@idr($bid->amount)</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="mt-6 rounded-2xl bg-ink-50 p-6 text-center text-sm text-ink-500">
                <p>No bids to refund.</p>
            </div>
        @endif

        {{-- Action Buttons --}}
        <div class="mt-8 flex gap-3">
            <a href="{{ route('admin.auctions.index') }}" class="rounded-full border border-ink-200 px-6 py-3 font-semibold text-ink-700 transition hover:border-ink-300">
                Cancel
            </a>
            <form method="POST" action="{{ route('admin.auctions.confirmRefund', $auction) }}" class="flex-1">
                @csrf
                @method('PATCH')
                <button type="submit" class="w-full rounded-full bg-gradient-to-r from-emerald-500 to-emerald-600 px-6 py-3 font-display font-black text-white shadow-lg transition hover:shadow-xl hover:-translate-y-0.5">
                    Refund All
                </button>
            </form>
        </div>
    </div>
</x-admin-layout>

