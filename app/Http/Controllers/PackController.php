<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;

/**
 * Digital card pack opening — modeled on the Pokémon TCG Pocket
 * mobile app's "open a pack" interaction. User opens a sealed
 * pack and reveals 5 random cards weighted by rarity.
 */
class PackController extends Controller
{
    public function index()
    {
        $preview = Card::query()->inRandomOrder()->limit(5)->get();

        return view('pages.packs.index', [
            'preview' => $preview,
        ]);
    }

    /**
     * TODO(team-backend): charge user (wallet / payment), roll 5 cards
     * weighted by rarity:
     *   - Common 60% / Uncommon 25% / Rare 10% / IR 4% / SIR ~1%
     * award them to the user's collection, then render the reveal view.
     */
    public function open(Request $request)
    {
        $pulls = Card::query()->inRandomOrder()->limit(5)->get();

        return view('pages.packs.reveal', [
            'pulls' => $pulls,
        ]);
    }
}
