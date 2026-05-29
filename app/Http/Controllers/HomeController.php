<?php

namespace App\Http\Controllers;

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

        $featuredItems = ShopItem::query()
            ->where('featured', true)
            ->where('is_active', true)
            ->limit(4)
            ->get();

        return view('pages.home', [
            'featuredCards' => $featuredCards,
            'featuredItems' => $featuredItems,
            'totalCards' => Card::query()->count(),
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
