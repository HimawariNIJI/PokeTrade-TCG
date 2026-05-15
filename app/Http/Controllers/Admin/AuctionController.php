<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Card;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Admin auction management — FRONTEND STUB.
 *
 * Every method returns hardcoded in-memory sample data so the Blade views
 * are fully clickable without a database. Method params are intentionally
 * NOT type-hinted as `Auction`, so route-model binding does not run while
 * there are no DB records.
 *
 * TODO(backend): replace each method body:
 *   index()      -> Auction::with('card','currentLeader')->latest()->paginate()
 *   create()     -> unchanged (renders an empty form)
 *   store()      -> validate (see rules below), create Auction, set status
 *   edit()       -> type-hint `Auction $auction`; load bids.user
 *   update()     -> validate + persist edits, INCLUDING the is_highlighted
 *                   toggle; when it is set true, clear is_highlighted on every
 *                   other auction so only one auction is highlighted at a time
 *   destroy()    -> delete or cancel the auction
 *   cardSearch() -> Card::where('name','like',"%{$q}%")->limit(24)->get(...)
 *
 * TODO(backend): add a migration adding `is_highlighted` (boolean, default
 * false) to the `auctions` table. It flags the single auction featured in the
 * hero banner on the public /auctions listing. When no auction has
 * is_highlighted = true, the listing falls back to the live auction with the
 * highest current_bid.
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
        return view('admin.auctions.index', ['auctions' => $this->sampleAuctions()]);
    }

    public function create()
    {
        return view('admin.auctions.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('admin.auctions.index')
            ->with('status', 'Auction published (stub) — backend wiring pending.');
    }

    public function edit($auction)
    {
        return view('admin.auctions.edit', ['auction' => $this->sampleAuction((int) $auction)]);
    }

    public function update(Request $request, $auction)
    {
        return redirect()->route('admin.auctions.index')
            ->with('status', 'Auction updated (stub) — backend wiring pending.');
    }

    public function destroy($auction)
    {
        return redirect()->route('admin.auctions.index')
            ->with('status', 'Auction removed (stub) — backend wiring pending.');
    }

    public function cardSearch(Request $request)
    {
        $q = strtolower(trim((string) $request->query('q', '')));
        $cards = $this->sampleCards();

        if ($q !== '') {
            $cards = array_values(array_filter(
                $cards,
                fn ($c) => str_contains(strtolower($c['name']), $q)
            ));
        }

        return response()->json(['data' => array_slice($cards, 0, 24)]);
    }

    // ================================================================
    // Sample data — TODO(backend): delete this entire section.
    // ================================================================

    private function sampleAuctions(): Collection
    {
        return collect([1, 2, 3])->map(fn ($id) => $this->sampleAuction($id));
    }

    private function sampleAuction(int $id): Auction
    {
        $statuses = [1 => 'live', 2 => 'scheduled', 3 => 'ended'];
        $names    = [1 => 'Charizard ex', 2 => 'Pikachu VMAX', 3 => 'Mewtwo GX'];

        $card = new Card([
            'name'        => $names[$id] ?? 'Eevee ex',
            'set_name'    => 'Obsidian Flames',
            'rarity'      => 'Illustration Rare',
            'image_small' => 'https://images.pokemontcg.io/sv3/6.png',
            'image_large' => 'https://images.pokemontcg.io/sv3/6_hires.png',
        ]);
        $card->id = 100 + $id;

        $auction = new Auction([
            'card_id'       => $card->id,
            'starting_bid'  => 500000,
            'current_bid'   => 4250000,
            'bid_increment' => 50000,
            'buy_now_price' => 9000000,
            'starts_at'     => Carbon::now()->subHours(3),
            'ends_at'       => Carbon::now()->addHours(2)->addMinutes(14),
            'status'        => $statuses[$id] ?? 'live',
        ]);
        $auction->id                = $id;
        $auction->current_leader_id = 901;
        // is_highlighted is not a real column yet (see TODO above); setting it
        // directly on the in-memory model makes it readable in the views.
        $auction->is_highlighted = ($id === 1);
        $auction->setRelation('card', $card);

        $leader = (new User(['name' => 'ashketchum_id']));
        $leader->id = 901;
        $auction->setRelation('currentLeader', $leader);

        $seller = new User(['name' => 'PokeTrade Admin']);
        $seller->id = 900;
        $auction->seller_id = 900;
        $auction->setRelation('seller', $seller);

        $bidders = [901 => 'ashketchum_id', 902 => 'misty_water', 903 => 'brock_rock', 904 => 'gary_oak'];
        $amounts = [4250000, 4100000, 3900000, 3500000];

        $bids = collect();
        $i = 0;
        foreach ($bidders as $uid => $name) {
            $user = new User(['name' => $name]);
            $user->id = $uid;

            $bid = new Bid(['auction_id' => $id, 'user_id' => $uid, 'amount' => $amounts[$i]]);
            $bid->id = $id * 10 + $i;
            $bid->created_at = Carbon::now()->subMinutes(($i + 1) * 7);
            $bid->setRelation('user', $user);

            $bids->push($bid);
            $i++;
        }
        $auction->setRelation('bids', $bids);

        return $auction;
    }

    private function sampleCards(): array
    {
        // Returns plain array maps (NOT Eloquent models) — consumed as JSON by cardSearch().
        $sets = ['Obsidian Flames', 'Paldea Evolved', '151', 'Paradox Rift'];
        $rarities = ['Illustration Rare', 'Special Illustration Rare', 'Ultra Rare', 'Double Rare'];
        $names = [
            'Charizard ex', 'Pikachu VMAX', 'Mewtwo GX', 'Eevee ex', 'Gardevoir ex',
            'Gengar VMAX', 'Lugia V', 'Rayquaza VMAX', 'Umbreon ex', 'Snorlax V',
            'Greninja ex', 'Tyranitar ex', 'Sylveon VMAX', 'Lucario VSTAR', 'Arceus V',
        ];

        $cards = [];
        foreach ($names as $idx => $name) {
            $cards[] = [
                'id'          => 101 + $idx,
                'name'        => $name,
                'number'      => str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT),
                'set_name'    => $sets[$idx % count($sets)],
                'rarity'      => $rarities[$idx % count($rarities)],
                'image_small' => 'https://images.pokemontcg.io/sv3/' . (($idx % 9) + 1) . '.png',
            ];
        }

        return $cards;
    }
}
