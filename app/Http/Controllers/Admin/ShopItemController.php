<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopItem;
use Illuminate\Http\Request;

class ShopItemController extends Controller
{
    public function index(Request $request)
    {
        $items = ShopItem::query()
            ->when($request->string('q')->toString(), fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.shop.index', compact('items'));
    }

    public function create()
    {
        return view('admin.shop.create');
    }

    /**
     * TODO(team-backend): Form Request validation including image upload.
     * Use Storage::disk('public')->put('shop-items', $file) and save the path.
     */
    public function store(Request $request)
    {
        return redirect()->route('admin.shop.index')->with('status', 'Item saved (stub).');
    }

    public function edit(ShopItem $shopItem)
    {
        return view('admin.shop.edit', ['item' => $shopItem]);
    }

    public function update(Request $request, ShopItem $shopItem)
    {
        return redirect()->route('admin.shop.index')->with('status', 'Item updated (stub).');
    }

    public function destroy(ShopItem $shopItem)
    {
        return back()->with('status', 'Item deleted (stub).');
    }
}
