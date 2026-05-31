<x-admin-layout heading="Auctions" eyebrow="Bidding console">
    <x-slot:actions>
        <x-prism-button :href="route('admin.auctions.create')" size="sm">+ New Auction</x-prism-button>
    </x-slot:actions>

    {{-- Auction Revenue Chart --}}
    <div class="mb-8 rounded-3xl border border-ink-200 bg-white p-6">
        <h3 class="font-display text-lg font-black">🎯 Auction Revenue (Last 6 Months)</h3>
        <p class="mt-1 text-sm text-ink-500">Revenue from winning bids in ended auctions</p>
        
        <div class="mt-6 flex items-end justify-between gap-2">
            @foreach($auctionRevenue as $m)
                @php $h = max(8, ($m['amount'] ?? 0) > 0 ? min(100, $m['amount'] / 1000) : 8); @endphp
                <div class="flex flex-1 flex-col items-center">
                    <div class="w-full rounded-t-md bg-gradient-to-b from-prism-mint to-prism-sky opacity-80" style="height: {{ $h }}px"></div>
                    <p class="mt-2 text-[11px] font-mono text-ink-500">{{ $m['month'] }}</p>
                    <p class="text-[10px] font-bold text-ink-700">@idr($m['amount'])</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Filter Buttons --}}
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('admin.auctions.index', ['filter' => 'all']) }}"
           class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition {{ $filter === 'all' ? 'bg-prism-violet text-white' : 'border border-ink-200 text-ink-700 hover:border-prism-violet hover:text-prism-violet' }}">
            All Auctions <span class="rounded-full bg-white/20 px-2 py-0.5 text-xs font-bold">{{ $statusCounts['all'] }}</span>
        </a>
        <a href="{{ route('admin.auctions.index', ['filter' => 'live']) }}"
           class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition {{ $filter === 'live' ? 'bg-rose-100 text-rose-700' : 'border border-ink-200 text-ink-700 hover:border-rose-300 hover:text-rose-600' }}">
            🔴 Live <span class="rounded-full {{ $filter === 'live' ? 'bg-rose-200' : 'bg-rose-50' }} px-2 py-0.5 text-xs font-bold">{{ $statusCounts['live'] }}</span>
        </a>
        <a href="{{ route('admin.auctions.index', ['filter' => 'scheduled']) }}"
           class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition {{ $filter === 'scheduled' ? 'bg-amber-100 text-amber-700' : 'border border-ink-200 text-ink-700 hover:border-amber-300 hover:text-amber-600' }}">
            Scheduled <span class="rounded-full {{ $filter === 'scheduled' ? 'bg-amber-200' : 'bg-amber-50' }} px-2 py-0.5 text-xs font-bold">{{ $statusCounts['scheduled'] }}</span>
        </a>
        <a href="{{ route('admin.auctions.index', ['filter' => 'ended']) }}"
           class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition {{ $filter === 'ended' ? 'bg-ink-200 text-ink-700' : 'border border-ink-200 text-ink-700 hover:border-ink-300' }}">
            Ended <span class="rounded-full {{ $filter === 'ended' ? 'bg-ink-300' : 'bg-ink-100' }} px-2 py-0.5 text-xs font-bold">{{ $statusCounts['ended'] }}</span>
        </a>
        <a href="{{ route('admin.auctions.index', ['filter' => 'cancelled']) }}"
           class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition {{ $filter === 'cancelled' ? 'bg-red-100 text-red-700' : 'border border-ink-200 text-ink-700 hover:border-red-300 hover:text-red-600' }}">
            Cancelled <span class="rounded-full {{ $filter === 'cancelled' ? 'bg-red-200' : 'bg-red-50' }} px-2 py-0.5 text-xs font-bold">{{ $statusCounts['cancelled'] }}</span>
        </a>
    </div>

    <p class="mb-4 text-xs text-ink-500">Showing 10 most recent auctions for selected filter</p>

    @php
        $emptyMessages = [
            'all' => 'No auctions yet. Open a card for bidding to get the hype started.',
            'live' => 'No live auctions at the moment.',
            'scheduled' => 'No scheduled auctions.',
            'ended' => 'No ended auctions.',
            'cancelled' => 'No cancelled auctions.',
        ];
        $emptyMessage = $emptyMessages[$filter] ?? 'No auctions found.';
    @endphp

    @if($auctions->isEmpty())
        <x-empty-state icon="⬢" title="No auctions found" :message="$emptyMessage">
            <x-prism-button :href="route('admin.auctions.create')" size="sm">+ New Auction</x-prism-button>
        </x-empty-state>
    @else
        <div class="overflow-hidden rounded-3xl border border-ink-200 bg-white">
            <table class="min-w-full text-sm">
                <thead class="bg-ink-50 text-left text-[10px] font-bold uppercase tracking-widest text-ink-500">
                    <tr>
                        <th class="px-4 py-3">Card</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Current bid</th>
                        <th class="px-4 py-3">Top bidder</th>
                        <th class="px-4 py-3">Highlighted</th>
                        <th class="px-4 py-3">Ends</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @foreach($auctions as $auction)
                        <tr class="hover:bg-ink-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-12 w-9 overflow-hidden rounded bg-ink-100">
                                        @if($auction->card?->image_small)
                                            <img src="{{ $auction->card->image_small }}" alt="" class="h-full w-full object-cover">
                                        @endif
                                    </span>
                                    <p class="font-bold">{{ $auction->card?->name ?? '—' }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusCls = [
                                        'live'      => 'bg-rose-100 text-rose-700',
                                        'scheduled' => 'bg-amber-100 text-amber-700',
                                        'ended'     => 'bg-ink-200 text-ink-700',
                                        'cancelled' => 'bg-ink-100 text-ink-500',
                                    ][$auction->status] ?? 'bg-ink-100 text-ink-500';
                                @endphp
                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-widest {{ $statusCls }}">
                                    {{ $auction->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono">@idr($auction->current_bid)</td>
                            <td class="px-4 py-3 text-ink-500">{{ $auction->currentLeader?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if($auction->is_highlighted)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-prism-pink/15 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-widest text-prism-pink">
                                        ⚡ Featured
                                    </span>
                                @else
                                    <span class="text-xs text-ink-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-ink-500">{{ $auction->ends_at?->diffForHumans() }}</td>
                            <td class="px-4 py-3 text-right space-x-3">
                                <a href="{{ route('admin.auctions.edit', $auction) }}"
                                class="text-xs font-semibold hover:text-prism-violet">
                                    Manage
                                </a>

                                @if(
                                    $auction->status === 'ended' &&
                                    $auction->refund_status !== 'approved'
                                )
                                    <form method="POST"
                                        action="{{ route('admin.auctions.refund', $auction) }}"
                                        class="inline">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                                onclick="return confirm('Refund all users for this auction?')"
                                                class="text-xs font-semibold text-emerald-600 hover:text-emerald-800">
                                            Refund
                                        </button>
                                    </form>
                                @elseif($auction->refund_status === 'approved')
                                    <span class="text-xs font-semibold text-emerald-600">
                                        Refunded
                                    </span>
                                @endif

                                <form method="POST"
                                    action="{{ route('admin.auctions.destroy', $auction) }}"
                                    class="inline">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            onclick="return confirm('Remove this auction?')"
                                            class="text-xs font-semibold text-red-600 hover:text-red-800">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-admin-layout>
