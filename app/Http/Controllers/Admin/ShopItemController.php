<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:booster,bundle,accessory,plush,other',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $image = $request->file('image');

            $imageName = Str::slug(
                pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME)
            ) . '.' . $image->getClientOriginalExtension();

            $imagePath = Storage::disk('public')
                ->putFileAs('shop', $image, $imageName);
        }

        $item = ShopItem::create([
            'name' => $validated['name'],
            'slug' => $this->generateSlug($validated['name']),
            'description' => $validated['description'],
            'category' => $validated['category'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'image' => $imagePath,
            'featured' => $request->boolean('featured'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.shop.index')->with('status', "Item '{$item->name}' created successfully.");
    }

    private function generateSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $count = 1;

        while (ShopItem::where('slug', $slug)->exists()) {
            $slug = "{$original}-{$count}";
            $count++;
        }

        return $slug;
    }

    public function edit(ShopItem $shopItem)
    {
        return view('admin.shop.edit', ['item' => $shopItem]);
    }

    public function update(Request $request, ShopItem $shopItem)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:booster,bundle,accessory,plush,other',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $imagePath = $shopItem->image;

        if ($request->hasFile('image')) {
            if ($shopItem->image) {
                Storage::disk('public')->delete($shopItem->image);
            }

            $image = $request->file('image');
            $imageName = Str::slug(
                pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME)
            ) . '.' . $image->getClientOriginalExtension();

            $imagePath = Storage::disk('public')
                ->putFileAs('shop', $image, $imageName);
        }

        $shopItem->update([
            'name' => $validated['name'],
            'slug' => $this->generateSlug($validated['name'], $shopItem->id),
            'description' => $validated['description'],
            'category' => $validated['category'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'image' => $imagePath,
            'featured' => $request->boolean('featured'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.shop.index')->with('status', "Item '{$shopItem->name}' updated successfully.");
    }

    public function destroy(ShopItem $shopItem)
    {  
        $shopItem->update(['is_deleted' => true]);
        return back()->with('status', "Item '{$shopItem->name}' deleted successfully.");
    }
}
