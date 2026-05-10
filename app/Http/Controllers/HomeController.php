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
        ]);
    }

    public function about()
    {
        return view('pages.about');
    }
}
