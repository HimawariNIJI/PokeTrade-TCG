<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use App\Models\ShopItem;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = $request->user()->cart()->with('items.itemable')->firstOrCreate([]);

        return view('pages.cart.index', [
            'cart' => $cart,
        ]);
    }

    /**
     * TODO(team-backend): add validation, stock checks, polymorphic resolution
     * snapshot price, increment quantity if exists.
     */
    public function add(Request $request)
    {
        $request->validate([
            'item_type' => 'required|string',
            'item_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        $quantity = $request->quantity;

        switch ($request->item_type) {
            case 'shop_item':
                $modelClass = ShopItem::class;
                break;

            default:
                return back()->with('status', 'Invalid item type.');
        }

        $product = $modelClass::findOrFail($request->item_id);

        $cart = $request->user()->cart()->firstOrCreate([]);

        $cartItem = $cart->items()
            ->where('itemable_id', $product->id)
            ->where('itemable_type', $modelClass)
            ->first();

        $existingQuantity = $cartItem ? $cartItem->quantity : 0;

        $totalQuantity = $existingQuantity + $quantity;

        if ($totalQuantity > $product->stock) {
            return back()->with('status', 'Requested quantity exceeds stock.');
        }

        if ($cartItem) {
            $cartItem->update([
                'quantity' => $totalQuantity,
            ]);
        } else {
            $cartItem = $cart->items()->create([
                'itemable_id' => $product->id,
                'itemable_type' => $modelClass,
                'quantity' => $quantity,
                'price_snapshot' => $product->price,
            ]);

            $cart->refresh();
        }

        return back()->with('status', 'Added to cart.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'item_type' => 'required|string',
            'item_id' => 'required|integer',
            'quantity' => 'required|integer|min:0',
        ]);

        $quantity = (int) $request->input('quantity');

        // If quantity is 0 or less, remove from cart
        if ($quantity < 1) {
            $this->remove($request);
            return back()->with('status', 'Item removed from cart.');
        }



        switch ($request->item_type) {
            case 'shop_item':
                $modelClass = ShopItem::class;
                break;

            default:
                return back()->with('status', 'Invalid item type.');
        }

        $product = $modelClass::findOrFail($request->item_id);

        if ($quantity > $product->stock) {
            return redirect()->back()->with(
                'status',
                'Requested quantity exceeds available stock.'
            );
        }

        $cart = $request->user()->cart()->firstOrCreate([]);
        $cartItem = $cart->items()
            ->where('itemable_id', $product->id)
            ->where('itemable_type', $modelClass)
            ->first();


        if (!$cartItem) {
            return back()->with('status', 'Item not found in cart.');
        }

        $cartItem->update([
            'quantity' => $quantity,
        ]);
        return back()->with('status', 'Cart updated.');
    }

    public function remove(Request $request)
    {
        $request->validate([
            'item_type' => 'required|string',
            'item_id' => 'required|integer',
        ]);

        switch ($request->item_type) {
            case 'shop_item':
                $modelClass = ShopItem::class;
                break;

            default:
                return back()->with('status', 'Invalid item type.');
        }

        $product = $modelClass::findOrFail($request->item_id);
        $cart = $request->user()->cart()->firstOrCreate([]);

        $cartItem = $cart->items()
            ->where('itemable_id', $product->id)
            ->where('itemable_type', $modelClass)
            ->first();

        if (!$cartItem) {
            return back()->with('status', 'Item not found in cart.');
        }

        $cartItem->delete();
        return back()->with('status', 'Item removed from cart.');
    }
}
