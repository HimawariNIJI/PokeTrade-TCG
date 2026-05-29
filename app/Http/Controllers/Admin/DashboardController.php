<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Order;
use App\Models\ShopItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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

        // Get last 6 months revenue aggregation by orders.paid_at
        $monthlyRevenue = collect(range(5, 0))->map(function ($i) {
            $startDate = now()->subMonths($i)->startOfMonth();
            $endDate = now()->subMonths($i)->endOfMonth();
            $month = $startDate->format('M');
            
            $amount = Order::where('payment_status', 'paid')
                ->whereBetween('paid_at', [$startDate, $endDate])
                ->sum('total');
            
            return [
                'month'  => $month,
                'amount' => (float) $amount,
            ];
        });

        return view('admin.dashboard', compact(
            'stats', 'recentOrders', 'bestSelling', 'mostExpensive', 'monthlyRevenue'
        ));
    }
}
