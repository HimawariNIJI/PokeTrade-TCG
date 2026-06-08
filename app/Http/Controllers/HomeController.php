<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Card;
use App\Models\ShopItem;

class HomeController extends Controller
{
    public function index()
    {
        $featuredCards = Card::query()
            ->where('featured', true)
            ->orWhereIn('rarity', [
                'Special Illustration Rare',
                'Hyper Rare',
                'Illustration Rare',
            ])
            ->limit(6)
            ->get();

        if ($featuredCards->isEmpty()) {
            $featuredCards = Card::query()->inRandomOrder()->limit(6)->get();
        }

        // Hero is hand-picked: Umbreon ex (PE SIR), Mega Charizard X ex (Phantasmal Flames SIR),
        // Pikachu Illustrator promo. Order is left → center → right; mobile shows the center card.
        $heroApiIds = ['sv8pt5-161', 'me2-125', 'basep-24'];
        $heroCards = Card::whereIn('api_id', $heroApiIds)
            ->get()
            ->sortBy(fn ($card) => array_search($card->api_id, $heroApiIds))
            ->values();

        $featuredItems = ShopItem::query()
            ->where('featured', true)
            ->where('is_active', true)
            ->limit(4)
            ->get();

        // Keep the home "Live auctions" panel in sync with the auctions page.
        Auction::settleDueStatuses();

        $featuredAuction = Auction::query()
            ->with('card', 'currentLeader')
            ->where('status', 'live')
            ->where('is_highlighted', true)
            ->first()
            ?? Auction::query()
                ->with('card', 'currentLeader')
                ->where('status', 'live')
                ->orderByDesc('current_bid')
                ->first();

        $liveAuctions = Auction::query()
            ->with('card', 'currentLeader')
            ->where('status', 'live')
            ->orderByDesc('current_bid')
            ->limit(3)
            ->get();

        return view('pages.home', [
            'featuredCards' => $featuredCards,
            'heroCards' => $heroCards,
            'featuredItems' => $featuredItems,
            'totalCards' => Card::query()->count(),
            'featuredAuction' => $featuredAuction,
            'liveAuctions' => $liveAuctions,
            'liveAuctionCount' => Auction::query()->where('status', 'live')->count(),
        ]);
    }

    public function about()
    {
        $eeveelutionOrder = [
            'Eevee ex', 'Vaporeon ex', 'Jolteon ex', 'Flareon ex',
            'Espeon ex', 'Umbreon ex', 'Leafeon ex', 'Glaceon ex', 'Sylveon ex',
        ];

        $sirCards = Card::query()
            ->where('rarity', 'Special Illustration Rare')
            ->whereIn('name', $eeveelutionOrder)
            ->get()
            ->keyBy('name');

        $eeveelutions = collect($eeveelutionOrder)
            ->map(fn ($name) => $sirCards->get($name))
            ->filter()
            ->values();

        $totalCards = Card::query()->count();
        $sirCount = Card::query()->where('rarity', 'Special Illustration Rare')->count();
        $hyperRareCount = Card::query()->where('rarity', 'Hyper Rare')->count();
        $artistCount = Card::query()->whereNotNull('artist')->distinct('artist')->count('artist');

        return view('pages.about', [
            'eeveelutions' => $eeveelutions,
            'totalCards' => $totalCards,
            'sirCount' => $sirCount,
            'hyperRareCount' => $hyperRareCount,
            'artistCount' => $artistCount,
        ]);
    }
}
