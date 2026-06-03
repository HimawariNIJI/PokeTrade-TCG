<x-admin-layout heading="Shop items" eyebrow="Custom merch">
    <x-slot:actions>
        <x-prism-button :href="route('admin.shop.create')" size="sm">+ New item</x-prism-button>
    </x-slot:actions>

    <form method="GET" class="mb-5 flex gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by name…" class="flex-1 rounded-full border-ink-200 text-sm">
        <button type="submit" class="rounded-full bg-ink-900 px-5 py-2 text-sm font-bold text-white">Search</button>
    </form>

    <div class="overflow-hidden rounded-3xl border border-ink-200 bg-white">
        <div class="overflow-x-auto [-webkit-overflow-scrolling:touch]">
            <table class="min-w-full text-sm">
                <thead class="bg-ink-50 text-left text-[10px] font-bold uppercase tracking-widest text-ink-500">
                    <tr>
                        <th class="px-4 py-3">Item</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3 text-right">Price</th>
                        <th class="px-4 py-3 text-right">Stock</th>
                        <th class="px-4 py-3 text-center">Featured</th>
                        <th class="px-4 py-3"></th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @foreach($items as $item)
                        <tr class="hover:bg-ink-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-12 w-12 overflow-hidden rounded-md bg-ink-100">
                                        @if($item->image)<img src="{{ asset('storage/' . $item->image) }}" class="h-full w-full object-cover">@endif
                                    </span>
                                    <div>
                                        <p class="font-bold whitespace-nowrap">{{ $item->name }}</p>
                                        <p class="text-[10px] text-ink-500 whitespace-nowrap">{{ $item->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs capitalize whitespace-nowrap">{{ $item->category }}</td>
                            <td class="px-4 py-3 text-right font-mono whitespace-nowrap">@idr($item->price)</td>
                            <td class="px-4 py-3 text-right font-mono whitespace-nowrap">{{ $item->stock }}</td>
                            <td class="px-4 py-3 text-center">@if($item->featured) ★ @endif</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.shop.edit', $item) }}"
                                class="text-xs font-semibold hover:text-prism-violet">
                                    Edit
                                </a>
                            </td>

                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.shop.destroy', $item) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-xs font-semibold text-red-600 hover:text-red-800"
                                            onclick="return confirm('Are you sure?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $items->links() }}</div>
</x-admin-layout>
