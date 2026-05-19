<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $cards = $request->user()->wishlistedCards()->latest('wishlists.created_at')->paginate(12);

        return view('pages.wishlist.index', [
            'cards' => $cards,
        ]);
    }

    /**
     * TODO(team-backend): toggle the wishlist row using attach/detach
     * via $request->user()->wishlistedCards()->toggle($card->id).
     */
    public function toggle(Request $request, Card $card)
    {
        $user = $request->user();

        $exists = $user->wishlistedCards()
        ->where('card_id', $card->id)
        ->exists();

        if($exists) {
            $user->wishlistedCards()->detach($card->id);
            return back()->with('status', 'Removed from wishlist');
        }

        $user->wishlistedCards()->attach($card->id);
        return back()->with('status', 'Wishlist updated');
    }
}
