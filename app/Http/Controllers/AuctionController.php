<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use Illuminate\Http\Request;
use App\Notifications\WishlistAuctionNotification;

class AuctionController extends Controller
{
    public function index()
    {
        $live = Auction::query()
            ->with('card', 'currentLeader')
            ->where('status', 'live')
            ->orderBy('ends_at')
            ->paginate(8, ['*'], 'live');

        $scheduled = Auction::query()
            ->with('card')
            ->where('status', 'scheduled')
            ->orderBy('starts_at')
            ->limit(8)
            ->get();

        $ended = Auction::query()
            ->with('card', 'currentLeader')
            ->where('status', 'ended')
            ->orderByDesc('ends_at')
            ->limit(6)
            ->get();

        return view('pages.auctions.index', compact('live', 'scheduled', 'ended'));
    }

    public function show(Auction $auction)
    {
        $auction->load('card', 'seller', 'bids.user');

        return view('pages.auctions.show', [
            'auction' => $auction,
        ]);
    }

    /**
     * TODO(team-backend): validate amount >= min_next_bid, create Bid,
     * update auction.current_bid + current_leader_id, broadcast WebSocket
     * event so other watchers get the new bid in real time.
     */
    public function bid(Request $request, Auction $auction)
    {
        return back()->with('status', 'Bid placed (stub) — websocket pending.');
    }
}
