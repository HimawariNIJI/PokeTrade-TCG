<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\CollectionCard;
use App\Support\Gacha;
use Illuminate\Http\Request;

/**
 * Digital gacha — modeled on the Pokémon TCG Pocket mobile app's
 * "open a pack" interaction. A trainer pulls a digital pack of 5
 * random cards; the cards are DIGITAL collectibles that land in
 * their personal collection (they are NOT the real, tracked cards
 * sold/auctioned elsewhere on the site).
 *
 * Pulls are rarity-weighted via {@see Gacha}. The first pull of each
 * day is free; the rest cost points earned from the merch store.
 */
class GachaController extends Controller
{
    public function index(Request $request)
    {
        $preview = Card::query()->inRandomOrder()->limit(5)->get();

        return view('pages.gacha.index', [
            'preview'           => $preview,
            'tiers'             => Gacha::rateTable(),
            'freePullAvailable' => (bool) $request->user()?->freeGachaAvailable(),
            'pullCost'          => Gacha::PULL_COST,
        ]);
    }

    /**
     * Roll a rarity-weighted pack and award it to the trainer's digital
     * collection (one CollectionCard row per card, source 'gacha').
     *
     * The first pull of the day is free; afterwards each pull costs
     * {@see Gacha::PULL_COST} points (earned from the merch store).
     */
    public function pull(Request $request)
    {
        $user = $request->user();

        if ($user->freeGachaAvailable()) {
            $user->last_free_gacha_at = now();
            $user->save();
        } else {
            if ($user->points < Gacha::PULL_COST) {
                return redirect()->route('gacha.index')->with(
                    'status',
                    "You've used today's free pull. Earn points by buying from the merch store to pull again."
                );
            }
            $user->points -= Gacha::PULL_COST;
            $user->save();
        }

        $pulls = Gacha::roll();
        $now = now();

        // How many copies of each pulled card the trainer held BEFORE this
        // pull — drives the "already owned?" badge on the reveal screen.
        $priorCounts = $user->collectionCards()
            ->whereIn('card_id', $pulls->pluck('id'))
            ->selectRaw('card_id, count(*) as total')
            ->groupBy('card_id')
            ->pluck('total', 'card_id');

        // Actually award the pulled cards to the trainer's collection.
        foreach ($pulls as $card) {
            CollectionCard::create([
                'user_id'     => $user->id,
                'card_id'     => $card->id,
                'source'      => 'gacha',
                'obtained_at' => $now,
            ]);
        }

        // Quantity held now = prior holdings + the copies drawn in this pull.
        $drawnThisPull = $pulls->countBy(fn (Card $card) => $card->id);
        $ownership = [];
        foreach ($pulls->unique('id') as $card) {
            $prior = (int) ($priorCounts[$card->id] ?? 0);
            $ownership[$card->id] = [
                'owned_before' => $prior > 0,
                'quantity'     => $prior + $drawnThisPull[$card->id],
            ];
        }

        return view('pages.gacha.reveal', [
            'pulls'             => $pulls,
            'ownership'         => $ownership,
            'freePullAvailable' => $user->freeGachaAvailable(),
            'pullCost'          => Gacha::PULL_COST,
        ]);
    }

    /**
     * Full gacha pull history — every pull as its own entry, sorted
     * earliest-first. Duplicate pulls are NOT collapsed: each
     * CollectionCard row is one pull and shows as its own line.
     */
    public function history(Request $request)
    {
        $pulls = $request->user()->collectionCards()
            ->with('card')
            ->orderBy('obtained_at')
            ->orderBy('id')
            ->paginate(30)
            ->withQueryString();

        return view('pages.gacha.history', [
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
