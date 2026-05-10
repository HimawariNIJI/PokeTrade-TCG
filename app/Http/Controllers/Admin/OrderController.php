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
     * TODO(team-backend): validate new status against allowed transitions,
     * stamp paid_at / shipped_at / delivered_at when crossing those states.
     */
    public function updateStatus(Request $request, Order $order)
    {
        return back()->with('status', 'Order status updated (stub).');
    }
}
