<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\CollectionCard;
use App\Support\Gacha;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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

        $allowedPerPage = [12, 24, 48, 96];
        $perPage = (int) $request->integer('per_page', 24);
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 24;
        }

        $page = $request->integer('page', 1);

        $allOwnedGlobal = $user->digitalCards()->orderBy('name')->get();

        $totalCards = $user->collectionCards()->count();

        $uniqueCards = $allOwnedGlobal->unique('id')->count();

        $rarityBreakdown = $allOwnedGlobal
            ->groupBy(fn ($card) => $card->rarity ?: 'Common')
            ->map->count()
            ->sortDesc();

        $allRarities = $allOwnedGlobal
            ->pluck('rarity')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $query = $user->digitalCards()
            ->when($request->filled('rarity'), function ($q) use ($request) {
                $q->where('rarity', $request->rarity);
            })->orderBy('name');

        $allOwned = $query->get();

        $unique = $allOwned->unique('id')->values();

        $cards = new LengthAwarePaginator(
            $unique->forPage($page, $perPage)->values(),
            $unique->count(),
            $perPage,
            $page,
        );

        $cards->withPath($request->url())->appends($request->query());

        $pinnedIds = collect($user->pinned_cards ?? [])->map(fn ($id) => (int) $id)->unique()->values();

        return view('pages.gacha.collection', [
            'cards'           => $cards,
            'totalCards'      => $totalCards,
            'uniqueCards'     => $uniqueCards,
            'rarityBreakdown' => $rarityBreakdown,
            'perPage'         => $perPage,
            'allowedPerPage'  => $allowedPerPage,
            'pinnedIds'       => $pinnedIds,
            'allRarities'     => $allRarities,
            'maxPinned'       => \App\Http\Controllers\SettingsController::MAX_PINNED,
        ]);
    }
}
