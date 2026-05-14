<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query()
            ->with('user')
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        return view('admin.orders.show', [
            'order' => $order->load('user', 'items.itemable'),
        ]);
    }

    /**
     * Validate status changes and stamp tracking timestamps.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', Order::STATUSES),
            'payment_status' => 'required|in:' . implode(',', Order::PAYMENT_STATUSES),
        ]);

        $status = $validated['status'];
        $paymentStatus = $validated['payment_status'];

        $attributes = [
            'status' => $status,
            'payment_status' => $paymentStatus,
        ];

        if ($paymentStatus === 'paid') {
            $attributes['paid_at'] = $order->paid_at ?? now();
        } else {
            $attributes['paid_at'] = null;
        }

        if ($status === 'shipped') {
            $attributes['shipped_at'] = $order->shipped_at ?? now();
            $attributes['delivered_at'] = null;
        } elseif ($status === 'delivered') {
            $attributes['shipped_at'] = $order->shipped_at ?? now();
            $attributes['delivered_at'] = $order->delivered_at ?? now();
        } else {
            $attributes['shipped_at'] = null;
            $attributes['delivered_at'] = null;
        }

        if ($status === 'cancelled') {
            $attributes['shipped_at'] = null;
            $attributes['delivered_at'] = null;
        }

        $order->update($attributes);

        return back()->with('status', 'Order status updated successfully.');
    }
}
