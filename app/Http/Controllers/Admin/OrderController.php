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

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', Order::STATUSES),
        ]);

        $newStatus = $validated['status'];
        $currentStatus = $order->status;

        // Prevent rollback status
        $allowedTransitions = [
            'pending' => [],
            'paid' => ['shipped', 'delivered'],
            'shipped' => ['delivered'],
            'delivered' => [],
            'cancelled' => [],
        ];

        if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
            return back()->with('error', 'Invalid status transition.');
        }

        $attributes = [
            'status' => $newStatus,
        ];

        if ($newStatus === 'shipped') {
            $attributes['shipped_at'] = $order->shipped_at ?? now();
        }

        if ($newStatus === 'delivered') {
            $attributes['shipped_at'] = $order->shipped_at ?? now();
            $attributes['delivered_at'] = $order->delivered_at ?? now();
        }

        $order->update($attributes);

        return back()->with('status', 'Order status updated successfully.');
    }
}
