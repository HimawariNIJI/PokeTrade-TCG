<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\OrderItem;
use App\Notifications\OrderPaidNotification;

class CheckoutController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $cart = $user->cart()->with('items.itemable')->first();

        // Default the shipping form to the user's most recent order, with a
        // fallback to the user's profile fields. Saves repeat data entry.
        $lastOrder = $user->orders()->latest()->first();

        $defaults = [
            'shipping_name'        => $lastOrder?->shipping_name        ?? $user->name,
            'shipping_phone'       => $lastOrder?->shipping_phone       ?? $user->phone,
            'shipping_address'     => $lastOrder?->shipping_address     ?? '',
            'shipping_city'        => $lastOrder?->shipping_city        ?? '',
            'shipping_postal_code' => $lastOrder?->shipping_postal_code ?? '',
        ];

        return view('pages.checkout.show', [
            'cart'     => $cart,
            'defaults' => $defaults,
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
        $request->validate([
            'shipping_phone' => [
                'required',
                'string',
                'regex:/^[0-9+\-\s]+$/',
            ],
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:100',
            'shipping_postal_code' => 'required|string|max:16',
            'payment_method' => 'required|in:midtrans',
        ], [
            'shipping_phone.regex' => 'Please enter a valid phone number.',
        ]);

        $cart = $request->user()->cart()->firstOrCreate([]);

        if (!$cart || $cart->items->isEmpty()) {
            return back()->with('status', 'Your cart is empty!');
        }

        DB::beginTransaction();

        try {
            // Calculate totals from actual cart data
            $subtotal = $cart->items->sum(function ($item) {
                return $item->price_snapshot * $item->quantity;
            });

            $shippingFee = 25000;
            $tax = $subtotal * 0.1;
            $total = $subtotal + $shippingFee + $tax;
            
            // Generate unique order code (timestamp + random string)
            $orderCode = 'PT-' . date('YmdHis') . '-' . strtoupper(Str::random(6));
            
            // Create order
            $order = Order::create([
                'code' => $orderCode,

                'user_id' => $request->user()->id,

                'status' => 'pending',
                'payment_status' => 'unpaid',

                'payment_method' => $request->payment_method,
                'payment_reference' => null,

                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'tax' => $tax,
                'total' => $total,

                // Shipping snapshot
                'shipping_name' => $request->user()->name,
                'shipping_phone' => $request->shipping_phone,
                'shipping_address' => $request->shipping_address,
                'shipping_city' => $request->shipping_city,
                'shipping_postal_code' => $request->shipping_postal_code,

                'notes' => $request->notes,

                'paid_at' => null,
                'shipped_at' => null,
                'delivered_at' => null,
            ]);

            // Store order items
            foreach ($cart->items as $cartItem) {

                $product = $cartItem->itemable;

                // Validate stock again before purchase
                if ($cartItem->quantity > $product->stock) {
                    DB::rollBack();

                    return back()->with(
                        'status',
                        $product->name . ' exceeds available stock.'
                    );
                }

                OrderItem::create([
                    'order_id' => $order->id,

                    'itemable_id' => $product->id,
                    'itemable_type' => get_class($product),

                    'name_snapshot' => $product->name,

                    'image_snapshot' => $product->image ?? null,

                    'price_snapshot' => $cartItem->price_snapshot,

                    'quantity' => $cartItem->quantity,

                    'subtotal' => $cartItem->price_snapshot * $cartItem->quantity,
                ]);

                // Decrement stock (reserve for pending order)
                $product->decrement('stock', $cartItem->quantity);
            }

            // Prepare item details for Midtrans
            $item_details = [];

            foreach ($cart->items as $cartItem) {

                $product = $cartItem->itemable;

                $item_details[] = [
                    'id'       => $product->id,
                    'price'    => (int) $cartItem->price_snapshot,
                    'quantity' => $cartItem->quantity,
                    'name'     => substr($product->name, 0, 50),
                ];
            }

            // Midtrans Config
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            // Midtrans transaction params
            $params = [
                'transaction_details' => [
                    'order_id' => $order->code,
                    'gross_amount' => (int) $order->total,
                ],

                'item_details' => $item_details,

                'customer_details' => [
                    'first_name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'phone' => $request->shipping_phone,
                ],
            ];

            // Generate Snap token
            $snapToken = \Midtrans\Snap::getSnapToken($params);

            // Save payment reference/token
            $order->update([
                'payment_reference' => $snapToken,
            ]);
            DB::commit();

            // Clear cart from database
            $cart->items()->delete();
            return view('pages.payment.index', compact('snapToken', 'order'));
        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with(
                'status',
                'Checkout failed: ' . $e->getMessage()
            );
        }
    }

    /**
     * Handle payment callback and update order status
     */
    public function paymentStatus(Request $request, Order $order)
    {
        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== $request->user()->id) {
            return redirect()->route('orders.index')->with('status', 'Unauthorized access.');
        }

        // Midtrans Config
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        try {
            $pointsearned = floor($order->subtotal / 10000);
            // Fetch transaction status from Midtrans using order code
            $transaction = \Midtrans\Transaction::status($order->code);

            // Map Midtrans transaction status to our payment status
            $transactionStatus = $transaction->transaction_status;

            if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
                $wasUnpaid = $order->payment_status !== 'paid';

                // Payment successful - stock remains decremented
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
                // Award points to user
                $order->user->increment('points', $pointsearned);

                // Fire the confirmation email + bell notification only on the
                // first unpaid→paid transition, so repeated callbacks don't spam.
                if ($wasUnpaid) {
                    $order->user->notify(new OrderPaidNotification($order));
                }
            } elseif ($transactionStatus === 'pending') {
                // Payment pending - stock remains reserved
                $order->update([
                    'payment_status' => 'unpaid',
                    'status' => 'pending',
                ]);
            } elseif ($transactionStatus === 'deny' || $transactionStatus === 'expire' || $transactionStatus === 'cancel') {
                // Payment failed - restore stock
                foreach ($order->items as $orderItem) {
                    $product = $orderItem->itemable;
                    if ($product) {
                        $product->increment('stock', $orderItem->quantity);
                    }
                }

                $order->update([
                    'payment_status' => 'failed',
                    'status' => 'cancelled',
                ]);
            }
        } catch (\Exception $e) {
            return redirect()->route('orders.show', $order->code)
                ->with('status', 'Could not verify payment status: ' . $e->getMessage());
        }
        if ($order->status == 'paid') {
            return redirect()->route('orders.show', $order->code)->with('status', "Payment successful! $pointsearned points earned!");
        } elseif ($order->status == 'pending') {
            return redirect()->route('orders.show', $order->code)->with('status', 'Payment is pending. Please complete it.');
        } else {
            return redirect()->route('orders.show', $order->code)->with('status', 'Payment failed or expired.');
        }
    }

    public function showPayment(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->payment_status !== 'unpaid') {
            return redirect()
                ->route('orders.show', $order->code)
                ->with('status', 'This order cannot be paid.');
        }

        return view('pages.payment.index', [
            'order' => $order,
            'snapToken' => $order->payment_reference,
        ]);
    }
}
