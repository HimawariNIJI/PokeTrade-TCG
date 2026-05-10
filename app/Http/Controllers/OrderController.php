<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->with('items')
            ->latest()
            ->paginate(10);

        return view('pages.orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order)
    {
        abort_unless($order->user_id === request()->user()->id, 403);

        return view('pages.orders.show', [
            'order' => $order->load('items.itemable'),
        ]);
    }
}
