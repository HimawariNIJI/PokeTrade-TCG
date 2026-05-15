<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Card;
use Illuminate\Http\Request;

/**
 * Admin auction management.
 *
 * Read paths are wired to the database: index() lists real auctions, edit()
 * loads a real auction, and cardSearch() queries the real `cards` table.
 *
 * The write paths — store(), update(), destroy() — are still STUBS: they flash
 * a message and redirect WITHOUT persisting. They are the remaining backend
 * work (validation, the is_highlighted migration, status transitions).
 *
 * TODO(backend): implement the write methods:
 *   store()   -> validate (see rules below), create the Auction, set status
 *   update()  -> validate + persist edits, INCLUDING the is_highlighted toggle;
 *                when it is set true, clear is_highlighted on every other
 *                auction so only one auction is highlighted at a time
 *   destroy() -> delete or cancel the auction
 *
 * TODO(backend): add a migration adding `is_highlighted` (boolean, default
 * false) to the `auctions` table. It flags the single auction featured in the
 * hero banner on the public /auctions listing. When no auction has
 * is_highlighted = true, the listing falls back to the live auction with the
 * highest current_bid. Until this column exists, the edit-form highlight
 * toggle always reads as off.
 *
 * TODO(backend): suggested validation rules for store()/update():
 *   'card_id'       => 'required|exists:cards,id'
 *   'starting_bid'  => 'required|numeric|min:0'
 *   'bid_increment' => 'required|numeric|min:1'
 *   'buy_now_price' => 'nullable|numeric|gt:starting_bid'
 *   'starts_at'     => 'required|date'
 *   'ends_at'       => 'required|date|after:starts_at'
 */
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
        return redirect()->route('admin.auctions.index')
            ->with('status', 'Auction publishing is not wired up yet — backend pending.');
    }

    public function edit(Auction $auction)
    {
        $auction->load('card', 'bids.user');

        return view('admin.auctions.edit', ['auction' => $auction]);
    }

    public function update(Request $request, Auction $auction)
    {
        return redirect()->route('admin.auctions.index')
            ->with('status', 'Auction editing is not wired up yet — backend pending.');
    }

    public function destroy(Auction $auction)
    {
        return redirect()->route('admin.auctions.index')
            ->with('status', 'Auction deletion is not wired up yet — backend pending.');
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
