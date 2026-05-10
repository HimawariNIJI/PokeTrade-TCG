<x-app-layout>

<section class="mx-auto max-w-[1200px] px-4 py-12 md:px-8 md:py-16">
    <a href="{{ route('auctions.index') }}" class="text-xs font-semibold text-ink-500 hover:text-ink-900">← Back to auctions</a>

    <div class="mt-6 grid gap-12 lg:grid-cols-12">
        <div class="lg:col-span-5">
            <div class="relative">
                <div class="absolute -inset-4 -z-10 rounded-[2.5rem] prism-bg opacity-40 blur-3xl"></div>
                <div class="overflow-hidden rounded-3xl bg-white p-3 shadow-2xl">
                    @if($auction->card?->image_large)
                        <img src="{{ $auction->card->image_large }}" alt="{{ $auction->card?->name }}" class="rounded-2xl">
                    @endif
                </div>
            </div>
        </div>

        <div class="lg:col-span-7">
            <div class="flex flex-wrap items-center gap-2">
                @if($auction->is_live)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-600 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-white">
                        <span class="h-2 w-2 animate-pulse rounded-full bg-white"></span> Live
                    </span>
                @else
                    <span class="rounded-full bg-ink-200 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-ink-700">{{ $auction->status }}</span>
                @endif
                <span class="text-xs text-ink-500">Listed by <strong class="text-ink-900">{{ $auction->seller?->name ?? 'PokeTrade' }}</strong></span>
            </div>

            <h1 class="mt-3 font-display text-5xl font-black tracking-tight">{{ $auction->card?->name }}</h1>

            <div class="mt-6 grid gap-3 rounded-3xl border border-ink-200 bg-white p-6 md:grid-cols-2">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-ink-500">Current bid</p>
                    <p class="mt-1 font-display text-4xl font-black prism-text">${{ number_format((float) $auction->current_bid, 2) }}</p>
                    @if($auction->currentLeader)
                        <p class="mt-1 text-xs text-ink-500">Leader: {{ $auction->currentLeader->name }}</p>
                    @endif
                </div>
                <div class="md:border-l md:border-ink-200 md:pl-6">
                    <p class="text-[10px] uppercase tracking-widest text-ink-500">Time remaining</p>
                    <p class="mt-1 font-display text-2xl font-bold">{{ $auction->ends_at?->diffForHumans() }}</p>
                    @if($auction->buy_now_price)
                        <p class="mt-1 text-xs text-ink-500">Buy it now: <strong>${{ number_format((float) $auction->buy_now_price, 2) }}</strong></p>
                    @endif
                </div>
            </div>

            @auth
                <form method="POST" action="{{ route('auctions.bid', $auction) }}" class="mt-5 flex flex-wrap items-end gap-3 rounded-3xl border border-ink-200 bg-white p-6">
                    @csrf
                    <label class="flex-1">
                        <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Your bid</span>
                        <input type="number" step="0.01" name="amount" min="{{ $auction->min_next_bid }}"
                               class="mt-1.5 w-full rounded-full border-ink-200 focus:border-prism-violet focus:ring-prism-violet"
                               placeholder="≥ ${{ number_format($auction->min_next_bid, 2) }}">
                    </label>
                    <x-prism-button type="submit" size="lg">Place bid</x-prism-button>
                </form>
            @else
                <div class="mt-5 rounded-3xl border border-ink-200 bg-ink-50 p-5 text-sm text-ink-700">
                    <a href="{{ route('login') }}" class="font-bold underline">Log in</a> to place a bid.
                </div>
            @endauth

            <h2 class="mt-10 font-display text-lg font-black">Bid history</h2>
            <div class="mt-3 max-h-80 overflow-y-auto rounded-2xl border border-ink-200 bg-white">
                @forelse($auction->bids as $bid)
                    <div class="flex items-center justify-between border-b border-ink-100 px-5 py-3 last:border-0">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full prism-bg text-xs font-bold text-white">
                                {{ Str::upper(Str::substr($bid->user?->name ?? '?', 0, 1)) }}
                            </span>
                            <div>
                                <p class="text-sm font-semibold">{{ $bid->user?->name ?? 'Anonymous' }}</p>
                                <p class="text-xs text-ink-500">{{ $bid->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <span class="font-mono font-bold">${{ number_format((float) $bid->amount, 2) }}</span>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-ink-500">No bids yet — be the first.</p>
                @endforelse
            </div>
        </div>
    </div>
</section>

</x-app-layout>
