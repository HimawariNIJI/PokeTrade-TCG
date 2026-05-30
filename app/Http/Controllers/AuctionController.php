<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuctionController extends Controller
{
    public function index()
    {
        Auction::settleDueStatuses();

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
            ->with('card', 'currentLeader', 'winner')
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
        $auction->load('card', 'seller', 'paidBids.user');

        return view('pages.auctions.show', [
            'auction' => $auction,
        ]);
    }

    /**
     * TODO(team-backend): broadcast WebSocket event so other
     * watchers get the new bid in real time.*/
    public function bid(Request $request, Auction $auction)
    {
        if ($auction->status !== 'live') {
            return response()->json([
                'message' => 'This auction is not live.'
            ], 422);
        }

        if (now()->lt($auction->starts_at) || now()->gte($auction->ends_at)) {
            return response()->json([
                'message' => 'This auction is closed.'
            ], 422);
        }

        if ($auction->seller_id === $request->user()->id) {
            return response()->json([
                'message' => 'You cannot bid on your own auction.'
            ], 422);
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

        try {  
            DB::beginTransaction();

            $orderId = 'auction-'.$auction->id.'-'.uniqid();

            $bid = Bid::create([
                'auction_id' => $auction->id,
                'user_id' => $request->user()->id,
                'amount' => $validated['amount'],
                'status' => Bid::STATUS_PENDING,
                'order_id' => $orderId,
            ]);

            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            $params = [
                'transaction_details' => [
                    'order_id' => $bid->order_id,
                    'gross_amount' => (int) round($bid->amount),
                ],
                'item_details' => [
                    [
                        'id' => 'auction_' . $auction->id,
                        'price' => (int) round($bid->amount),
                        'quantity' => 1,
                        'name' => 'Auction bid for ' . ($auction->card?->name ?? 'auction'),
                    ],
                ],
                'customer_details' => [
                    'first_name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'phone' => $request->user()->phone,
                ],
            ];

            $snapToken = \Midtrans\Snap::getSnapToken($params);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Unable to initiate payment. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }

        $auction->load([
            'paidBids.user',
            'currentLeader'
        ]);

        $leaderboard = $auction->paidBids
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
            'message' => 'Bid placed successfully. Complete your payment to confirm the bid.',
            'current_bid' => $auction->current_bid,
            'min_next_bid' => $auction->min_next_bid,
            'leaderboard' => $leaderboard,
            'latest_bid' => $latestBid,
            'snap_token' => $snapToken,
            'auction_status' => $auction->status,
        ]);
    }

    /**
     * Legacy "Pay now" endpoint. Auctions are now settled automatically
     * on close — the winning bid acts as the order payment and an Order
     * is created during snapshotWinner(). This route stays for any
     * cached UI / bookmarks and just forwards to the resulting order.
     */
    public function pay(Request $request, Auction $auction)
    {
        $auction->snapshotWinner();

        if (! $auction->isWinner($request->user()->id)) {
            abort(403, 'Only the winner can view this auction order.');
        }

        if ($order = $auction->winnerOrder()) {
            return redirect()->route('orders.show', $order->code);
        }

        return back()->with('status', 'Your auction order is being prepared. Refresh in a moment.');
    }

    /**
     * Winner requests a refund. Only allowed while the refund window is
     * open and no prior request exists.
     */
    public function requestRefund(Request $request, Auction $auction)
    {
        if (! $auction->isWinner($request->user()->id)) {
            abort(403, 'Only the winner can request a refund.');
        }

        if (! $auction->isRefundWindowOpen()) {
            return back()->withErrors([
                'refund' => 'This auction is not eligible for refund right now.',
            ]);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $auction->update([
            'refund_status'       => 'requested',
            'refund_reason'       => $validated['reason'],
            'refund_requested_at' => now(),
        ]);

        return back()->with('status', 'Refund request submitted. An admin will review it.');
    }

    /**
     * Refresh auction data for AJAX polling. Returns updated auction
     * info including status, bids, and leaderboard.
     */
    public function refresh(Auction $auction)
    {


        if ($auction->status === 'scheduled' && now()->gte($auction->starts_at)) {
            $auction->update(['status' => 'live']);
            $auction->refresh();
        }

        // Auto end if expired
        if ($auction->status === 'live' && now()->gte($auction->ends_at)) {
            $auction->snapshotWinner();
            $auction->update(['status' => 'ended']);
            $auction->refresh();
        }

        $auction->load('card', 'seller', 'paidBids.user', 'currentLeader');

        $rankedBids = $auction->paidBids->sortByDesc('amount')->values();
        $topUniqueBids = $rankedBids
            ->unique('user_id')
            ->take(3)
            ->values();

        $leaderboard = $topUniqueBids->map(function ($bid) use ($auction) {
            return [
                'user' => $bid->user?->name ?? 'Anonymous',
                'amount' => $bid->amount,
                'is_leader' => $bid->user_id === $auction->current_leader_id,
            ];
        });

        $feedBids = $auction->paidBids->sortByDesc('created_at')->take(20)->values();
        $bidFeed = $feedBids->map(function ($bid) {
            return [
                'user' => $bid->user?->name ?? 'Anonymous',
                'amount' => $bid->amount,
                'time' => $bid->created_at?->diffForHumans(),
            ];
        });

        return response()->json([
            'success' => true,
            'status' => $auction->status,
            'is_live' => $auction->is_live,
            'current_bid' => $auction->current_bid,
            'min_next_bid' => $auction->min_next_bid,
            'current_leader' => $auction->currentLeader?->name ?? 'Anonymous',
            'current_leader_id' => $auction->current_leader_id,
            'ends_at' => $auction->ends_at?->toIso8601String(),
            'winner_id' => $auction->winner_id,
            'leaderboard' => $leaderboard,
            'bid_feed' => $bidFeed,
        ]);
    }

    /**
     * End an auction when its time expires. Snapshots the winner and
     * marks it as ended. Called via AJAX when the countdown reaches zero.
     */
    public function end(Request $request, Auction $auction)
    {
        // Allow the request if the auction is still live and has actually expired
        if ($auction->status !== 'live' || now()->lt($auction->ends_at)) {
            return response()->json([
                'message' => 'This auction cannot be ended at this time.'
            ], 422);
        }

        // Snapshot winner and update status
        $auction->snapshotWinner();
        $auction->update(['status' => 'ended']);

        return response()->json([
            'success' => true,
            'message' => 'Auction ended.',
            'status' => 'ended',
            'winner_id' => $auction->winner_id,
        ]);
    }
}
