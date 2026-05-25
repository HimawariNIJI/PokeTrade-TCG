<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\CollectionCard;
use Illuminate\Http\Request;

/**
 * Digital gacha — modeled on the Pokémon TCG Pocket mobile app's
 * "open a pack" interaction. A trainer pulls a digital pack of 5
 * random cards; the cards are DIGITAL collectibles that land in
 * their personal collection (they are NOT the real, tracked cards
 * sold/auctioned elsewhere on the site).
 */
class GachaController extends Controller
{
    public function index()
    {
        $preview = Card::query()->inRandomOrder()->limit(5)->get();

        return view('pages.gacha.index', [
            'preview' => $preview,
        ]);
    }

    /**
     * Roll 5 random cards and award them to the trainer's digital
     * collection (one CollectionCard row per pull, source 'gacha').
     *
     * TODO(team-backend): charge the user + weight the roll by rarity
     * (Common 60% / Uncommon 25% / Rare 10% / IR 4% / SIR ~1%).
     * The roll weighting stays stubbed — plain random is fine for now —
     * but the awarding-to-collection below IS the real implementation.
     */
    public function pull(Request $request)
    {
        if ($request->user()->points < 10) {
            return redirect()->route('gacha.index')->with('status', 'Not enough points to pull a pack!');
        }
        $request->user()->points -= 10;
        $request->user()->save();
        
        $pulls = Card::query()->inRandomOrder()->limit(5)->get();

        $user = $request->user();
        $now = now();

        // Actually award the pulled cards to the trainer's collection.
        foreach ($pulls as $card) {
            CollectionCard::create([
                'user_id'     => $user->id,
                'card_id'     => $card->id,
                'source'      => 'gacha',
                'obtained_at' => $now,
            ]);
        }

        return view('pages.gacha.reveal', [
            'pulls' => $pulls,
        ]);
    }

    /**
     * The trainer's digital collection — every card pulled from the
     * gacha — plus a few simple roll-up stats.
     */
    public function collection(Request $request)
    {
        $user = $request->user();

        // digitalCards() is BelongsToMany via the collection_cards pivot,
        // so it yields one row per pull — collapse to distinct cards.
        $owned = $user->digitalCards()->orderBy('name')->get();
        $unique = $owned->unique('id')->values();

        // Total = every pull (duplicates included); unique = distinct cards.
        $totalCards = $user->collectionCards()->count();
        $uniqueCards = $unique->count();

        // Count of pulls grouped by card rarity (duplicates counted).
        $rarityBreakdown = $owned
            ->groupBy(fn (Card $card) => $card->rarity ?: 'Common')
            ->map->count()
            ->sortDesc();

        // Per-page selector — clamp to a known list so users can't request 99999.
        $allowedPerPage = [12, 24, 48, 96];
        $perPage = (int) $request->integer('per_page', 24);
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 24;
        }

        $cards = new \Illuminate\Pagination\LengthAwarePaginator(
            $unique->forPage($request->integer('page', 1), $perPage)->values(),
            $unique->count(),
            $perPage,
            $request->integer('page', 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('pages.gacha.collection', [
            'cards'           => $cards,
            'totalCards'      => $totalCards,
            'uniqueCards'     => $uniqueCards,
            'rarityBreakdown' => $rarityBreakdown,
            'perPage'         => $perPage,
            'allowedPerPage'  => $allowedPerPage,
        ]);
    }
}
