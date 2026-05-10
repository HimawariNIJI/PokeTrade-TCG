<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function show(Request $request)
    {
        $cart = $request->user()->cart()->with('items.itemable')->first();

        return view('pages.checkout.show', [
            'cart' => $cart,
        ]);
    }

    /**
     * TODO(team-backend): validate shipping form, create Order + OrderItems
     * inside a DB transaction, snapshot prices, decrement stock,
     * empty the cart, kick off payment gateway (Midtrans sandbox or Stripe test),
     * redirect to /orders/{code}/payment.
     */
    public function place(Request $request)
    {
        return redirect()->route('orders.index')
            ->with('status', 'Order placed (stub) — backend logic pending.');
    }
}
