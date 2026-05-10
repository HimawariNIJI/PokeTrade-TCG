<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;

class CardController extends Controller
{
    /**
     * Catalog page — search, filter by type/supertype/rarity, paginate.
     */
    public function index(Request $request)
    {
        $query = Card::query();

        if ($search = $request->string('q')->toString()) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($type = $request->string('type')->toString()) {
            // JSON contains works on SQLite + MySQL
            $query->whereJsonContains('types', $type);
        }

        if ($supertype = $request->string('supertype')->toString()) {
            $query->where('supertype', $supertype);
        }

        if ($rarity = $request->string('rarity')->toString()) {
            $query->where('rarity', $rarity);
        }

        $sort = $request->string('sort', 'number')->toString();
        match ($sort) {
            'price_asc'  => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name'       => $query->orderBy('name'),
            'rarity'     => $query->orderByRaw("CASE WHEN rarity LIKE '%Special Illustration%' THEN 1 WHEN rarity LIKE '%Hyper%' THEN 2 WHEN rarity LIKE '%Illustration%' THEN 3 WHEN rarity LIKE '%Ultra%' THEN 4 ELSE 5 END"),
            default      => $query->orderByRaw('CAST(number AS UNSIGNED) ASC'),
        };

        $cards = $query->paginate(24)->withQueryString();

        $allTypes = Card::query()
            ->whereNotNull('types')
            ->pluck('types')
            ->flatten()
            ->unique()
            ->filter()
            ->sort()
            ->values();

        $allRarities = Card::query()
            ->whereNotNull('rarity')
            ->pluck('rarity')
            ->unique()
            ->filter()
            ->sort()
            ->values();

        return view('pages.cards.index', [
            'cards' => $cards,
            'allTypes' => $allTypes,
            'allRarities' => $allRarities,
        ]);
    }

    public function show(Card $card)
    {
        $related = Card::query()
            ->where('id', '!=', $card->id)
            ->when($card->types, fn ($q) => $q->whereJsonContains('types', $card->types[0] ?? ''))
            ->limit(4)
            ->get();

        return view('pages.cards.show', [
            'card' => $card,
            'related' => $related,
        ]);
    }
}
