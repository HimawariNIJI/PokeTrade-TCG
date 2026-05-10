<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Order;
use App\Models\ShopItem;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'cards'        => Card::count(),
            'shop_items'   => ShopItem::count(),
            'customers'    => User::where('role', User::ROLE_CUSTOMER)->count(),
            'orders'       => Order::count(),
            'revenue'      => (float) Order::where('payment_status', 'paid')->sum('total'),
            'pending'      => Order::where('status', 'pending')->count(),
        ];

        $recentOrders = Order::with('user')->latest()->limit(8)->get();

        $bestSelling = Card::query()
            ->orderByDesc('featured')
            ->orderByDesc('market_price')
            ->limit(5)
            ->get();

        $mostExpensive = Card::query()
            ->orderByDesc('market_price')
            ->limit(5)
            ->get();

        // Last 6 months revenue stub — friend can wire to actual order totals
        $monthlyRevenue = collect(range(5, 0))->map(fn ($i) => [
            'month'  => now()->subMonths($i)->format('M'),
            'amount' => 0,
        ]);

        return view('admin.dashboard', compact(
            'stats', 'recentOrders', 'bestSelling', 'mostExpensive', 'monthlyRevenue'
        ));
    }
}
