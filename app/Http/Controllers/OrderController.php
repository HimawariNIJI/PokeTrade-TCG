<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->orders()->with('items');
        
        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        
        $orders = $query->latest()->paginate(10)->withQueryString();

        return view('pages.orders.index', [
            'orders' => $orders,
            'statuses' => Order::STATUSES,
            'selectedStatus' => $request->string('status')->toString(),
        ]);
    }

    public function show(Order $order)
    {
        abort_unless(auth()->id() == $order->user_id, 403);

        return view('pages.orders.show', [
            'order' => $order->load('items.itemable'),
        ]);
    }
    public function cancel(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (
            $order->status !== 'pending' ||
            $order->payment_status !== 'unpaid'
        ) {
            return back()->with('status', 'This order cannot be cancelled.');
        }

        foreach ($order->items as $orderItem) {
            $product = $orderItem->itemable;
            if ($product) {
                $product->increment('stock', $orderItem->quantity);
            }
        }

        $order->update([
            'status' => 'cancelled',
            'payment_status' => 'failed',
        ]);

        return back()->with('status', 'Order cancelled successfully.');
    }
}
