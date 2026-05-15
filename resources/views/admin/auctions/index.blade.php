<x-admin-layout heading="Auctions" eyebrow="Bidding console">
    <x-slot:actions>
        <x-prism-button :href="route('admin.auctions.create')" size="sm">+ New Auction</x-prism-button>
    </x-slot:actions>

    @if($auctions->isEmpty())
        <x-empty-state icon="⬢" title="No auctions yet" message="Open a card for bidding to get the hype started.">
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
                        <th class="px-4 py-3">Highlighted bidder</th>
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
                            <td class="px-4 py-3">
                                <span class="font-semibold">{{ $auction->currentLeader?->name ?? '—' }}</span>
                                <span class="ml-1 text-[10px] uppercase tracking-wide text-ink-400">
                                    ({{ $auction->highlight_mode ?? 'auto' }})
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-ink-500">{{ $auction->ends_at?->diffForHumans() }}</td>
                            <td class="px-4 py-3 text-right space-x-3">
                                <a href="{{ route('admin.auctions.edit', $auction) }}"
                                   class="text-xs font-semibold hover:text-prism-violet">Manage</a>
                                <form method="POST" action="{{ route('admin.auctions.destroy', $auction) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            onclick="return confirm('Remove this auction?')"
                                            class="text-xs font-semibold text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-admin-layout>
