<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Card;
use App\Models\User;
use App\Notifications\WishlistAuctionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;


class AuctionController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');
        
        $query = Auction::query()
            ->with('card', 'currentLeader');
        
        // Apply filter
        if ($filter !== 'all' && in_array($filter, ['live', 'scheduled', 'cancelled', 'ended'])) {
            $query->where('status', $filter);
        }
        
        // Limit to 10 most recent auctions
        $auctions = $query->latest()->limit(10)->get();
        
        // Get counts for each status
        $statusCounts = [
            'all' => Auction::count(),
            'live' => Auction::where('status', 'live')->count(),
            'scheduled' => Auction::where('status', 'scheduled')->count(),
            'ended' => Auction::where('status', 'ended')->count(),
            'cancelled' => Auction::where('status', 'cancelled')->count(),
        ];
        
        // Get auction revenue from winning bids (ended auctions only)
        $baseDate = now()->startOfMonth();

        $auctionRevenue = collect(range(5, 0))->map(function ($i) use ($baseDate) {
            $date = $baseDate->copy()->subMonthsNoOverflow($i);

            return [
                'month' => $date->format('M'),
                'amount' => (float) Auction::where('status', 'ended')
                    ->whereBetween('ends_at', [
                        $date->copy()->startOfMonth(),
                        $date->copy()->endOfMonth(),
                    ])
                    ->sum('winning_amount'),
            ];
        });
        
        return view('admin.auctions.index', [
            'auctions' => $auctions,
            'filter' => $filter,
            'statusCounts' => $statusCounts,
            'auctionRevenue' => $auctionRevenue,
        ]);
    }

    public function create()
    {
        return view('admin.auctions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'card_id'       => ['required', 'exists:cards,id'],
            'starting_bid'  => ['required', 'numeric', 'min:0'],
            'bid_increment' => ['required', 'numeric', 'min:1'],
            'buy_now_price' => ['nullable', 'numeric', 'gt:starting_bid'],
            'starts_at'     => ['required', 'date'],
            'ends_at'       => ['required', 'date', 'after:starts_at'],
        ]);

        $existingAuction = Auction::where('card_id', $validated['card_id'])
            ->whereIn('status', ['live', 'scheduled'])
            ->exists();

        if ($existingAuction) {
            return back()->withErrors([
                'card_id' => 'Card already has an active auction.'
            ]);
        }

        $startsAt = Carbon::parse($validated['starts_at']);
        $endsAt = Carbon::parse($validated['ends_at']);
        $status = $startsAt->isFuture()
            ? 'scheduled'
            : ($endsAt->isPast() ? 'ended' : 'live');

        $auction = Auction::create([
            'card_id'          => $validated['card_id'],
            'seller_id'        => $request->user()->id,
            'starting_bid'     => $validated['starting_bid'],
            'current_bid'      => $validated['starting_bid'],
            'bid_increment'    => $validated['bid_increment'],
            'buy_now_price'    => $validated['buy_now_price'] ?? null,
            'starts_at'        => $startsAt,
            'ends_at'          => $endsAt,
            'status'           => $status,
        ]);

        // Notify everyone who wishlisted this card — sends both the in-app
        // database notification (for the bell) and an email, via WishlistAuctionNotification.
        $auction->load('card');
        $wishers = User::whereIn(
            'id',
            DB::table('wishlists')->where('card_id', $auction->card_id)->pluck('user_id')
        )->get();

        if ($wishers->isNotEmpty()) {
            Notification::send($wishers, new WishlistAuctionNotification($auction));
        }

        return redirect()->route('admin.auctions.index')
            ->with('status', 'Auction successfully published.');
    }

    public function edit(Auction $auction)
    {
        $auction->load('card', 'bids.user');

        return view('admin.auctions.edit', ['auction' => $auction]);
    }

    public function update(Request $request, Auction $auction)
    {
        if ($auction->status === 'ended') {
            return back()->withErrors([
                'auction' => 'Ended auctions cannot be edited.'
            ]);
        }

        $validated = $request->validate([
            'starting_bid'  => ['required', 'numeric', 'min:0'],
            'bid_increment' => ['required', 'numeric', 'min:1'],
            'buy_now_price' => ['nullable', 'numeric', 'gt:starting_bid'],
            'status'        => ['required', 'in:' . implode(',', Auction::STATUSES)],
            'starts_at'     => ['required', 'date'],
            'ends_at'       => ['required', 'date', 'after:starts_at'],
        ]);

        $allowedTransitions = [
            'scheduled' => ['live', 'cancelled'],
            'live' => ['ended', 'cancelled'],
            'ended' => [],
            'cancelled' => ['scheduled', 'live', 'ended',],
        ];

        $oldStatus = $auction->status;
        $newStatus = $validated['status'];
        $hasBids = $auction->bids()->exists();

        if ($hasBids && $newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            return back()->withErrors([
                'status' => 'Auctions with bids cannot be cancelled.'
            ]);
        }

        if ($newStatus !== $oldStatus && !in_array($newStatus, $allowedTransitions[$oldStatus])) {
            return back()->withErrors([
                'status' => 'Invalid status transition.'
            ]);
        }

        if ($newStatus === 'live' && Carbon::parse($validated['ends_at'])->isPast()) {
            return back()->withErrors([
                'status' => 'Cannot set auction to live because the end time has already passed.'
            ]);
        }

        if ($newStatus === 'scheduled' && Carbon::parse($validated['starts_at'])->isPast()) {
            return back()->withErrors([
                'status' => 'Scheduled auctions must start in the future.'
            ]);
        }

        if ($auction->bids()->exists()) {
            $changingBidSettings =
                $validated['starting_bid'] != $auction->starting_bid ||
                $validated['bid_increment'] != $auction->bid_increment;

            if ($changingBidSettings) {
                return back()->withErrors([
                    'starting_bid' => 'Cannot change bid settings after bids exist.'
                ]);
            }
        }

        if ($request->boolean('is_highlighted')) {
            Auction::where('id', '!=', $auction->id)
                ->update([
                    'is_highlighted' => false
                ]);
        }

        $auction->update([
            'starting_bid'   => $validated['starting_bid'],
            'bid_increment'  => $validated['bid_increment'],
            'buy_now_price'  => $validated['buy_now_price'] ?? null,
            'starts_at'      => Carbon::parse($validated['starts_at']),
            'ends_at'        => Carbon::parse($validated['ends_at']),
            'status'         => $newStatus,
            'is_highlighted' => $request->boolean('is_highlighted'),
        ]);

        // Stamp the winner the moment the auction ends.
        if ($newStatus === 'ended' && $oldStatus !== 'ended') {
            $auction->refresh()->snapshotWinner();
        }

        return redirect()->route('admin.auctions.index')
            ->with('status', 'Auction successfully updated.');
    }

    public function destroy(Auction $auction)
    {
        if ($auction->bids()->exists()) {
            return back()->withErrors([
                'auction' => 'Auctions with bids cannot be deleted.'
            ]);
        }

        $auction->delete();

        return redirect()->route('admin.auctions.index')->with('status', 'Auction successfully deleted.');
    }

    /**
     * Admin resolves a refund request — approve releases the funds, reject
     * keeps the sale final. Both stamp resolved_at so the audit trail is
     * complete.
     */
    public function resolveRefund(Request $request, Auction $auction)
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
        ]);

        if ($auction->refund_status !== 'requested') {
            return back()->withErrors([
                'refund_status' => 'No pending refund request on this auction.',
            ]);
        }

        $auction->update([
            'refund_status'       => $validated['decision'],
            'refund_resolved_at'  => now(),
        ]);

        return back()->with(
            'status',
            $validated['decision'] === 'approved'
                ? 'Refund approved.'
                : 'Refund rejected.'
        );
    }

    /**
     * Searches the real card catalogue for the card picker. Returns up to 24
     * matches as JSON; an empty query returns the first cards alphabetically.
     */
    public function cardSearch(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $cards = Card::query()
            ->when($q !== '', fn($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->limit(24)
            ->get(['id', 'name', 'set_name', 'rarity', 'image_small']);

        return response()->json(['data' => $cards]);
    }

    public function refund(Auction $auction)
    {
        // Redirect to index (not back()) so a browser back/forward into this
        // page after the auction is already refunded lands somewhere sensible
        // instead of bouncing off a stale referrer.
        if ($auction->status !== 'ended') {
            return redirect()->route('admin.auctions.index')
                ->with('error', 'Auction must be ended first.');
        }

        if ($auction->refund_status === 'approved') {
            return redirect()->route('admin.auctions.index')
                ->with('status', 'Auction already refunded.');
        }

        // Get all non-winning bids (loser bids)
        $loserBids = $auction->bids()
            ->where('user_id', '!=', $auction->winner_id)
            ->select('user_id', DB::raw('MAX(amount) as amount'))
            ->groupBy('user_id')
            ->with('user')
            ->orderByDesc('amount')
            ->get();

        return view('admin.auctions.refund-confirm', [
            'auction' => $auction,
            'loserBids' => $loserBids,
        ]);
    }

    public function confirmRefund(Auction $auction)
    {
        if ($auction->status !== 'ended') {
            return redirect()->route('admin.auctions.index')
                ->with('error', 'Auction must be ended first.');
        }

        if ($auction->refund_status === 'approved') {
            return redirect()->route('admin.auctions.index')
                ->with('status', 'Auction already refunded.');
        }

        $auction->update([
            'refund_status' => 'approved',
            'refund_resolved_at' => now(),
        ]);

        return redirect()->route('admin.auctions.index')
            ->with('status', 'Successfully refunded all users for this auction.');
    }
}
