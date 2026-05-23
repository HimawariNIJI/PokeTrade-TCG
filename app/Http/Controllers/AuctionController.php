<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Http\Request;
use App\Notifications\WishlistAuctionNotification;

class AuctionController extends Controller
{
    public function index()
    {
        // Hero / highlighted auction
        $highlighted = Auction::query()
            ->with('card', 'currentLeader')
            ->where('status', 'live')
            ->where('is_highlighted', true)
            ->first();

        // Fallback:
        // highest current bid
        if (!$highlighted) {
            $highlighted = Auction::query()
                ->with('card', 'currentLeader')
                ->where('status', 'live')
                ->orderByDesc('current_bid')
                ->first();
        }

        // Live auctions grid
        $live = Auction::query()
            ->with('card', 'currentLeader')
            ->where('status', 'live')

            // Remove hero auction from grid
            ->when($highlighted, function ($query) use ($highlighted) {
                $query->where('id', '!=', $highlighted->id);
            })

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

        return view('pages.auctions.index', [
            'highlighted' => $highlighted,
            'live' => $live,
            'scheduled' => $scheduled,
            'ended' => $ended,
        ]);
    }

    public function show(Auction $auction)
    {
        $auction->load('card', 'seller', 'bids.user');

        return view('pages.auctions.show', [
            'auction' => $auction,
        ]);
    }

    /**
     * TODO(team-backend): broadcast WebSocket event so other
     * watchers get the new bid in real time.*/
    public function bid(Request $request, Auction $auction)
    {
        // if ($auction->status !== 'live') {
        //     return back()->withErrors([
        //         'auction' => 'This auction is not live.'
        //     ]);
        // }

        // if (now()->lt($auction->starts_at) || now()->gte($auction->ends_at)) {
        //     return back()->withErrors([
        //         'auction' => 'This auction is closed.'
        //     ]);
        // }

        if ($auction->seller_id === $request->user()->id) {
            return back()->withErrors([
                'auction' => 'You cannot bid on your own auction.'
            ]);
        }

        $validated = $request->validate([
            'amount' => ['required','numeric','min:0',
                function ($attribute, $value, $fail) use ($auction) {
                    if ($value < $auction->min_next_bid) {
                        $fail(sprintf(
                            'The bid must be at least %s.',
                            number_format($auction->min_next_bid, 2, '.', ',')
                        ));
                    }
                },
            ],
        ]);

        Bid::create([
            'auction_id' => $auction->id,
            'user_id' => $request->user()->id,
            'amount' => $validated['amount'],
        ]);

        $auction->current_bid = $validated['amount'];
        $auction->current_leader_id = $request->user()->id;
        $auction->save();

        $auction->load([
            'bids.user',
            'currentLeader'
        ]);

        $leaderboard = $auction->bids
            ->sortByDesc('amount')
            ->unique('user_id')
            ->take(3)
            ->values()
            ->map(function ($bid) use ($auction) {
                return [
                    'user' => $bid->user?->name ?? 'Anonymous',
                    'amount' => $bid->amount,
                    'is_leader' => $bid->user_id === $auction->current_leader_id,
                ];
            });


        $latestBid = [
            'user' => $request->user()->name,
            'amount' => $validated['amount'],
            'time' => 'just now',
        ];
        
        return response()->json([
            'success' => true,
            'message' => 'Bid placed successfully.',
            'current_bid' => $auction->current_bid,
            'min_next_bid' => $auction->min_next_bid,
            'leaderboard' => $leaderboard,
            'latest_bid' => $latestBid,
        ]);
    }
}
