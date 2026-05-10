<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;

class GachaController extends Controller
{
    public function index()
    {
        // Sample preview cards for the gacha landing page
        $preview = Card::query()->inRandomOrder()->limit(5)->get();

        return view('pages.gacha.index', [
            'preview' => $preview,
        ]);
    }

    /**
     * TODO(team-backend): charge user (deduct from wallet or via payment),
     * roll 5 cards based on rarity weights:
     *   - Common 60% / Uncommon 25% / Rare 10% / IR 4% / SIR 1%
     * award them as inventory entries (or unique-owned-cards table — TBD),
     * return the pulled cards to the show view for the reveal animation.
     */
    public function pull(Request $request)
    {
        $pulls = Card::query()->inRandomOrder()->limit(5)->get();

        return view('pages.gacha.reveal', [
            'pulls' => $pulls,
        ]);
    }
}
