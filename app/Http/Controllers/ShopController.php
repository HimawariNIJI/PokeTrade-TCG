<?php

namespace App\Http\Controllers;

use App\Models\ShopItem;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = ShopItem::query()->where('is_active', true);

        if ($category = $request->string('category')->toString()) {
            $query->where('category', $category);
        }

        if ($search = $request->string('q')->toString()) {
            $query->where('name', 'like', "%{$search}%");
        }

        $items = $query->orderByDesc('featured')->paginate(12)->withQueryString();

        return view('pages.shop.index', [
            'items' => $items,
            'categories' => ShopItem::CATEGORIES,
        ]);
    }

    public function show(ShopItem $shopItem)
    {
        return view('pages.shop.show', [
            'item' => $shopItem,
        ]);
    }
}
