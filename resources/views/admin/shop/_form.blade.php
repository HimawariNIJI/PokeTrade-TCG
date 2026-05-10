@php $item = $item ?? null; @endphp

<form method="POST" action="{{ $item ? route('admin.shop.update', $item) : route('admin.shop.store') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if($item) @method('PATCH') @endif

    <div class="grid gap-6 lg:grid-cols-12">
        <div class="lg:col-span-7 space-y-4 rounded-3xl border border-ink-200 bg-white p-6">
            <h2 class="font-display text-base font-black">Item information</h2>

            <label class="block">
                <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Name</span>
                <input type="text" name="name" required value="{{ old('name', $item?->name) }}" class="mt-1.5 w-full rounded-xl border-ink-200">
            </label>

            <div class="grid gap-4 md:grid-cols-2">
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Category</span>
                    <select name="category" class="mt-1.5 w-full rounded-xl border-ink-200">
                        @foreach(\App\Models\ShopItem::CATEGORIES as $c)
                            <option value="{{ $c }}" @selected(old('category', $item?->category) === $c)>{{ ucfirst($c) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Price ($)</span>
                    <input type="number" step="0.01" name="price" required value="{{ old('price', $item?->price ?? '0.00') }}" class="mt-1.5 w-full rounded-xl border-ink-200">
                </label>
                <label class="block">
                    <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Stock</span>
                    <input type="number" name="stock" value="{{ old('stock', $item?->stock ?? 0) }}" class="mt-1.5 w-full rounded-xl border-ink-200">
                </label>
            </div>

            <label class="block">
                <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Description</span>
                <textarea name="description" rows="4" class="mt-1.5 w-full rounded-xl border-ink-200">{{ old('description', $item?->description) }}</textarea>
            </label>

            <label class="block">
                <span class="text-xs font-bold uppercase tracking-widest text-ink-700">Product image</span>
                <input type="file" name="image" accept="image/*" class="mt-1.5 block w-full rounded-xl border border-ink-200 px-4 py-2.5 text-sm">
                <span class="text-[11px] text-ink-500">Required by rubric for shop merch (custom-uploaded). PNG/JPG up to 4 MB.</span>
            </label>
        </div>

        <aside class="lg:col-span-5 space-y-4">
            @if($item?->image)
                <div class="rounded-3xl border border-ink-200 bg-white p-4">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-ink-500">Current image</p>
                    <img src="{{ asset('storage/' . $item->image) }}" class="mt-2 aspect-square w-full rounded-xl object-cover">
                </div>
            @endif

            <div class="rounded-3xl border border-ink-200 bg-white p-6">
                <label class="inline-flex cursor-pointer items-center gap-2">
                    <input type="checkbox" name="featured" value="1" @checked(old('featured', $item?->featured)) class="rounded border-ink-300 text-prism-violet">
                    <span class="text-sm font-semibold">Featured on home</span>
                </label>
                <label class="mt-3 inline-flex cursor-pointer items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item?->is_active ?? true)) class="rounded border-ink-300 text-prism-violet">
                    <span class="text-sm font-semibold">Active in store</span>
                </label>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.shop.index') }}" class="rounded-full border border-ink-200 px-5 py-2.5 text-sm font-bold">Cancel</a>
                <x-prism-button type="submit" size="md">{{ $item ? 'Save changes' : 'Create item' }}</x-prism-button>
            </div>
        </aside>
    </div>
</form>
