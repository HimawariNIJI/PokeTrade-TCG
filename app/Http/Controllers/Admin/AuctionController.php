<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AuctionController extends Controller
{
    public function index()
    {
        $auctions = Auction::query()
            ->with('card', 'currentLeader')
            ->latest()
            ->get();

        return view('admin.auctions.index', ['auctions' => $auctions]);
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

        Auction::create([
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

        if ($auction->bids()->exists()) {

            $changingBidSettings =
                $validated['starting_bid'] != $auction->starting_bid ||

                $validated['bid_increment'] != $auction->bid_increment;

            if ($changingBidSettings) {

                return back()->withErrors([
                    'starting_bid' =>
                        'Cannot change bid settings after bids exist.'
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
            'status'         => $validated['status'],
            'is_highlighted' => $request->boolean('is_highlighted'),
        ]);

        return redirect()->route('admin.auctions.index')
            ->with('status', 'Auction successfully updated.');
    }

    public function destroy(Auction $auction)
    {
        $auction->delete();

        return redirect()->route('admin.auctions.index')->with('status', 'Auction successfully deleted.');
    }

    /**
     * Searches the real card catalogue for the card picker. Returns up to 24
     * matches as JSON; an empty query returns the first cards alphabetically.
     */
    public function cardSearch(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $cards = Card::query()
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->limit(24)
            ->get(['id', 'name', 'set_name', 'rarity', 'image_small']);

        return response()->json(['data' => $cards]);
    }
}
