<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = $request->user()->cart()->with('items.itemable')->first();

        return view('pages.cart.index', [
            'cart' => $cart,
        ]);
    }

    /**
     * TODO(team-backend): add validation, stock checks, polymorphic resolution
     * to either Card or ShopItem, snapshot price, increment quantity if exists.
     */
    public function add(Request $request)
    {
        // Minimal stub — friend will flesh this out.
        return back()->with('status', 'Added to cart (stub).');
    }

    public function update(Request $request, int $itemId)
    {
        // TODO(team-backend): validate qty 1..stock, recompute totals.
        return back()->with('status', 'Cart updated (stub).');
    }

    public function remove(Request $request, int $itemId)
    {
        // TODO(team-backend): delete item, redirect with flash.
        return back()->with('status', 'Item removed (stub).');
    }
}
