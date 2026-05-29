<x-app-layout>

<section class="mx-auto max-w-[1200px] px-4 py-10 md:px-8">
    <a href="{{ route('auctions.index') }}" class="text-xs font-semibold text-ink-500 hover:text-ink-900">← Back to auctions</a>

    @if ($errors->any())
        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $rankedBids = $auction->bids->sortByDesc('amount')->values();
        $feedBids   = $auction->bids->sortByDesc('created_at')->take(20);
    @endphp

    <div
        x-data="auctionCountdown('{{ $auction->ends_at?->toIso8601String() }}')"
        class="arena-surface relative mt-5 overflow-hidden rounded-[2rem] border border-prism-violet/40 p-6 text-ink-50 shadow-2xl md:p-10"
    >
        {{-- ambient glow blobs --}}
        <div class="pointer-events-none absolute -left-24 -top-24 h-64 w-64 rounded-full bg-prism-pink/30 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 -right-20 h-72 w-72 rounded-full bg-prism-sky/20 blur-3xl"></div>

        <div class="relative grid gap-8 lg:grid-cols-12">
            {{-- Card --}}
            <div class="lg:col-span-5">
                <div class="relative">
                    <div class="absolute -inset-3 -z-10 rounded-3xl prism-bg opacity-60 blur-2xl"></div>
                    <div class="overflow-hidden rounded-3xl bg-ink-900/60 p-3 ring-1 ring-white/10">
                        @if($auction->card?->image_large)
                            <img src="{{ $auction->card->image_large }}" alt="{{ $auction->card?->name }}" class="rounded-2xl">
                        @endif
                    </div>
                </div>
            </div>

            {{-- Bid arena --}}
            <div class="lg:col-span-7">
                @if($auction->is_live)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-prism-mint px-3 py-1 text-[11px] font-black uppercase tracking-widest text-ink-900">
                        <span class="h-2 w-2 animate-ping rounded-full bg-ink-900"></span> Live Now
                    </span>
                @else
                    <span class="rounded-full bg-white/10 px-3 py-1 text-[11px] font-black uppercase tracking-widest">{{ $auction->status }}</span>
                @endif

                <h1 class="mt-3 font-display text-4xl font-black tracking-tight md:text-5xl">{{ $auction->card?->name }}</h1>
                <p class="mt-1 text-sm text-white/50">Listed by {{ $auction->seller?->name ?? 'PokeTrade' }}</p>

                {{-- Current bid + timer + buy now --}}
                <div class="mt-6 flex flex-wrap items-end gap-x-10 gap-y-4">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/40">Current Bid</p>
                        <p id="current-bid" class="animate-bid-counter mt-1 bg-gradient-to-r from-prism-mint to-prism-sky bg-clip-text font-display text-5xl font-black text-transparent">
                            @idr($auction->current_bid)
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/40">Ends In</p>
                        <p class="mt-1 font-mono text-2xl font-bold text-prism-pink" x-text="display">—</p>
                    </div>
                    @if($auction->buy_now_price)
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/40">Buy Now</p>
                            <p class="mt-1 font-display text-2xl font-bold text-prism-gold">@idr($auction->buy_now_price)</p>
                        </div>
                    @endif
                </div>

                {{-- Top bidders leaderboard --}}
                <div class="mt-7">
                    <p class="text-xs font-black uppercase tracking-widest text-prism-pink">🔥 Top Bidders</p>
                    <div id="leaderboard" class="mt-2 space-y-1.5">
                        @php
                            $topUniqueBids = $rankedBids
                                ->unique('user_id')
                                ->take(3);
                        @endphp
                        @forelse($topUniqueBids as $i => $bid)
                            @php $isLeader = $bid->user_id === $auction->current_leader_id; @endphp
                            <div class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm
                                {{ $isLeader
                                    ? 'border border-prism-pink bg-gradient-to-r from-prism-pink/25 to-prism-violet/15 shadow-[0_0_18px_-2px] shadow-prism-pink/50'
                                    : 'bg-white/5' }}">
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full text-[11px] font-black
                                    {{ $i === 0 ? 'bg-gradient-to-br from-prism-gold to-prism-pink text-ink-900' : 'bg-white/10' }}">
                                    {{ $isLeader ? '👑' : $i + 1 }}
                                </span>
                                <span class="font-bold">{{ $bid->user?->name ?? 'Anonymous' }}</span>
                                @if($isLeader)
                                    <span class="rounded-full bg-prism-pink/30 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider">Winning</span>
                                @endif
                                <span class="ml-auto font-mono font-bold">@idr($bid->amount)</span>
                            </div>
                        @empty
                            <p class="rounded-xl bg-white/5 px-3 py-4 text-center text-sm text-white/50">No bids yet — be the first to strike ⚡</p>
                        @endforelse
                    </div>
                </div>

                {{-- Bid form --}}
                @auth
                    <form id="bid-form" method="POST" action="{{ route('auctions.bid', $auction) }}" class="mt-6 flex flex-wrap items-end gap-3">
                        @csrf
                        <label class="flex-1">
                            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/40">Your bid</span>
                            <input type="number" step="{{ $auction->bid_increment }}" name="amount" min="{{ $auction->min_next_bid }}"
                                   placeholder="≥ @idr($auction->min_next_bid)"
                                   class="mt-1 w-full rounded-full border-white/15 bg-white/10 text-ink-50 placeholder-white/30 focus:border-prism-mint focus:ring-prism-mint">
                        </label>
                        <button type="submit"
                                class="rounded-full bg-gradient-to-r from-prism-pink to-prism-mint px-8 py-3 font-display font-black text-ink-900 shadow-[0_0_24px_-4px] shadow-prism-pink/70 transition hover:-translate-y-0.5">
                            Place Your Bid ⚡
                        </button>
                        {{-- Error --}}
                        <p id="bid-error"
                            class="text-sm font-semibold text-red-400">
                        </p>
                        {{-- Success --}}
                        <p id="bid-success"
                            class="text-sm font-semibold text-green-400">
                        </p>
                    </form>
                @else
                    <div class="mt-6 rounded-2xl bg-white/5 p-4 text-sm text-white/70">
                        <a href="{{ route('login') }}" class="font-bold text-prism-mint underline">Log in</a> to join the bidding war.
                    </div>
                @endauth
            </div>
        </div>

        {{-- Winner: pay + refund flow ===================================== --}}
        @auth
            @if($auction->status === 'ended' && $auction->isWinner(auth()->id()))
                <div class="relative mt-8 rounded-2xl border border-prism-mint/40 bg-prism-mint/10 p-5 text-ink-50">
                    <p class="text-xs font-black uppercase tracking-widest text-prism-mint">🏆 You won this auction</p>

                    @if(! $auction->isPaid())
                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <p class="text-sm">
                                Winning bid <span class="font-mono font-bold">@idr($auction->winning_amount ?? $auction->current_bid)</span>.
                                Pay to claim the card.
                            </p>
                            <form method="POST" action="{{ route('auctions.pay', $auction) }}">
                                @csrf
                                <button type="submit"
                                        class="rounded-full bg-gradient-to-r from-prism-mint to-prism-sky px-6 py-2 font-display font-black text-ink-900 shadow transition hover:-translate-y-0.5">
                                    Pay now
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif
        @endauth

        {{-- Live bid feed --}}
        <div class="relative mt-8 border-t border-white/10 pt-5">
            <p class="text-xs font-black uppercase tracking-widest text-prism-sky">⚡ Live Bid Feed</p>
            <div id="bid-feed" class="mt-2 max-h-56 space-y-1 overflow-y-auto">
                @forelse($feedBids as $bid)
                    <div class="flex items-center gap-2 rounded-lg bg-white/5 px-3 py-1.5 text-xs">
                        <span class="text-prism-mint">⚡</span>
                        <span class="font-bold">{{ $bid->user?->name ?? 'Anonymous' }}</span>
                        <span class="text-white/40">bid</span>
                        <span class="font-mono font-bold text-prism-sky">@idr($bid->amount)</span>
                        <span class="ml-auto text-white/30">{{ $bid->created_at?->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="px-3 py-3 text-xs text-white/40">Bids will appear here as they happen.</p>
                @endforelse
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const auctionId = {{ $auction->id }};
        const refreshUrl = '{{ route("auctions.refresh", $auction) }}';
        const endUrl = '{{ route("auctions.end", $auction) }}';
        const form = document.getElementById('bid-form');
        const statusEl = document.querySelector('[class*="rounded-full bg"]'); // Status badge
        let refreshInterval = null;
        let hasEnded = false;

        // Auto-refresh auction data every 3 seconds
        function startAutoRefresh() {
            refreshInterval = setInterval(async () => {
                try {
                    const response = await fetch(refreshUrl, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                        }
                    });

                    if (!response.ok) return;

                    const data = await response.json();

                    // Update status if it changed
                    if (data.status === 'ended' && !hasEnded) {
                        await endAuction();
                        return;
                    }

                    // Update current bid
                    const currentBidEl = document.getElementById('current-bid');
                    if (currentBidEl && data.current_bid) {
                        currentBidEl.textContent = 'Rp ' + Number(data.current_bid).toLocaleString('id-ID');
                    }

                    // Update input minimum
                    const amountInput = form?.querySelector('input[name="amount"]');
                    if (amountInput && data.min_next_bid) {
                        amountInput.min = data.min_next_bid;

                        amountInput.placeholder =
                            '≥ Rp ' + Number(data.min_next_bid).toLocaleString('id-ID');
                    }

                    // Update leaderboard
                    const leaderboardEl = document.getElementById('leaderboard');
                    if (leaderboardEl && data.leaderboard && data.leaderboard.length > 0) {
                        leaderboardEl.innerHTML = '';
                        data.leaderboard.forEach((bid, index) => {
                            leaderboardEl.innerHTML +=
                            `<div class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm
                                ${bid.is_leader
                                    ? 'border border-prism-pink bg-gradient-to-r from-prism-pink/25 to-prism-violet/15'
                                    : 'bg-white/5'}">

                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full text-[11px] font-black
                                    ${index === 0
                                        ? 'bg-gradient-to-br from-prism-gold to-prism-pink text-ink-900'
                                        : 'bg-white/10'}">
                                    ${bid.is_leader ? '👑' : index + 1}
                                </span>

                                <span class="font-bold">${bid.user}</span>

                                ${bid.is_leader ? `
                                    <span class="rounded-full bg-prism-pink/30 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider">
                                        Winning
                                    </span>` : ''}

                                <span class="ml-auto font-mono font-bold">
                                    Rp ${Number(bid.amount).toLocaleString('id-ID')}
                                </span>
                            </div>`;
                        });
                    }

                    // Update bid feed
                    const feedEl = document.getElementById('bid-feed');
                    if (feedEl && data.bid_feed && data.bid_feed.length > 0) {
                        feedEl.innerHTML = '';
                        data.bid_feed.forEach((bid) => {
                            feedEl.innerHTML +=
                            `<div class="flex items-center gap-2 rounded-lg bg-white/5 px-3 py-1.5 text-xs">
                                <span class="text-prism-mint">⚡</span>
                                <span class="font-bold">${bid.user}</span>
                                <span class="text-white/40">bid</span>
                                <span class="font-mono font-bold text-prism-sky">
                                    Rp ${Number(bid.amount).toLocaleString('id-ID')}
                                </span>
                                <span class="ml-auto text-white/30">${bid.time}</span>
                            </div>`;
                        });
                    }
                } catch (error) {
                    console.error('Error refreshing auction:', error);
                }
            }, 3000); // Refresh every 3 seconds
        }

        // End auction and update UI
        async function endAuction() {
            if (hasEnded) return;
            hasEnded = true;

            try {
                const response = await fetch(endUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });

                if (!response.ok) return;

                // Update status badge
                const statusEl = document.querySelector('span[class*="rounded-full"]');
                if (statusEl) {
                    statusEl.innerHTML = '<span class="rounded-full bg-white/10 px-3 py-1 text-[11px] font-black uppercase tracking-widest">Ended</span>';
                }

                // Disable bid form
                if (form) {
                    form.style.display = 'none';
                    const container = form.parentElement;
                    if (container) {
                        container.innerHTML += '<div class="mt-6 rounded-2xl bg-white/5 p-4 text-sm text-white/70">This auction has ended.</div>';
                    }
                }

                // Reload page after 2 seconds to show winner section
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } catch (error) {
                console.error('Error ending auction:', error);
            }
        }

        // Check if auction should be ended based on countdown
        if (window.auctionCountdown) {
            // Get the Alpine.js countdown data
            const endsAtEl = document.querySelector('[x-data*="auctionCountdown"]');
            if (endsAtEl) {
                // We'll check on each refresh if the time has expired
                const checkExpired = setInterval(() => {
                    const endsAtStr = '{{ $auction->ends_at?->toIso8601String() }}';
                    if (endsAtStr) {
                        const endsAt = new Date(endsAtStr);
                        const now = new Date();
                        if (now > endsAt && !hasEnded) {
                            clearInterval(checkExpired);
                            clearInterval(refreshInterval);
                            endAuction();
                        }
                    }
                }, 1000);
            }
        }

        // Start auto-refresh
        startAutoRefresh();

        // Bid form submission
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const errorEl = document.getElementById('bid-error');
            const successEl = document.getElementById('bid-success');

            errorEl.textContent = '';
            successEl.textContent = '';

            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;

            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (!response.ok) {
                    if (data.errors) {
                        errorEl.textContent = Object.values(data.errors).flat().join(' ');
                    }
                    else {
                        errorEl.textContent = data.message || 'Failed to place bid.';
                    }
                    submitBtn.disabled = false;
                    return;
                }

                successEl.textContent = data.message;

                // Update current bid
                const currentBidEl = document.getElementById('current-bid');
                if (currentBidEl) {
                    currentBidEl.textContent = 'Rp ' + Number(data.current_bid).toLocaleString('id-ID');
                }

                // Update input minimum
                const amountInput = form.querySelector('input[name="amount"]');
                amountInput.min = data.min_next_bid;
                amountInput.placeholder = '≥ Rp ' + Number(data.min_next_bid).toLocaleString('id-ID');
                amountInput.value = '';

                const leaderboardEl = document.getElementById('leaderboard');
                if (leaderboardEl) {
                    leaderboardEl.innerHTML = '';

                    data.leaderboard.forEach((bid, index) => {
                        leaderboardEl.innerHTML +=
                        `<div class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm
                            ${bid.is_leader
                                ? 'border border-prism-pink bg-gradient-to-r from-prism-pink/25 to-prism-violet/15'
                                : 'bg-white/5'}">

                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full text-[11px] font-black
                                ${index === 0
                                    ? 'bg-gradient-to-br from-prism-gold to-prism-pink text-ink-900'
                                    : 'bg-white/10'}">
                                ${bid.is_leader ? '👑' : index + 1}
                            </span>

                            <span class="font-bold">${bid.user}</span>

                            ${bid.is_leader ? `
                                <span class="rounded-full bg-prism-pink/30 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider">
                                    Winning
                                </span>` : ''}

                            <span class="ml-auto font-mono font-bold">
                                Rp ${Number(bid.amount).toLocaleString('id-ID')}
                            </span>
                        </div>`;
                    });
                }

                const feedEl = document.getElementById('bid-feed');
                if (feedEl) {
                    feedEl.insertAdjacentHTML(
                        'afterbegin',
                        `<div class="flex items-center gap-2 rounded-lg bg-white/5 px-3 py-1.5 text-xs">
                            <span class="text-prism-mint">⚡</span>
                            <span class="font-bold">${data.latest_bid.user}</span>
                            <span class="text-white/40">bid</span>
                            <span class="font-mono font-bold text-prism-sky">
                                Rp ${Number(data.latest_bid.amount).toLocaleString('id-ID')}
                            </span>
                            <span class="ml-auto text-white/30">just now</span>
                        </div>`
                    );
                }

                if (data.snap_token && window.snap) {
                    window.snap.pay(data.snap_token, {
                        onSuccess: function(result) {
                            window.location.reload();
                        },
                        onPending: function(result) {
                            window.location.reload();
                        },
                        onError: function(result) {
                            errorEl.textContent = 'Payment failed. Please try again.';
                            submitBtn.disabled = false;
                        },
                        onClose: function() {
                            errorEl.textContent = 'Payment was not completed.';
                            submitBtn.disabled = false;
                        }
                    });
                } else {
                    submitBtn.disabled = false;
                }
            } catch (error) {
                errorEl.textContent = 'Something went wrong.';
                submitBtn.disabled = false;
            }

            submitBtn.disabled = false;
            submitBtn.textContent = 'Place Your Bid ⚡';
        });
    });
</script>

<script
    src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
    data-client-key="{{ config('midtrans.client_key') }}">
</script>
</x-app-layout>